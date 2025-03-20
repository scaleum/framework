<?php
declare (strict_types = 1);
/**
 * This file is part of Scaleum Framework.
 *
 * (C) 2009-2025 Maxim Kirichenko <kirichenko.maxim@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Scaleum\Http\Client\Transport;

use Scaleum\Http\OutboundRequest;
use Scaleum\Http\InboundResponse;
use Scaleum\Http\HeadersManager;
use Scaleum\Http\Stream;
use Scaleum\Http\Uri;
use Scaleum\Stdlib\Exceptions\EHttpException;
use Scaleum\Stdlib\Exceptions\ERuntimeError;
use Scaleum\Stdlib\Helpers\HttpHelper;
use Scaleum\Stdlib\Helpers\JsonHelper;
use Scaleum\Stdlib\Helpers\UrlHelper;

/**
 * SocketTrasport
 *
 * @author Maxim Kirichenko <kirichenko.maxim@gmail.com>
 */
class SocketTrasport extends TransportAbstract {
    protected ?string $authType = null;
    protected ?string $password = null;
    protected ?string $username = null;
    protected ?string $token    = null;

    /**
     * Get the value of authType
     */
    public function getAuthType(): ?string {
        return $this->authType;
    }

    /**
     * Set the value of authType
     *
     * @return  self
     */
    public function setAuthType(string $authType): static
    {
        $this->authType = $authType;

        return $this;
    }

    /**
     * Get the value of password
     */
    public function getPassword(): ?string {
        return $this->password;
    }

    /**
     * Set the value of password
     *
     * @return  self
     */
    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Get the value of username
     */
    public function getUsername(): ?string {
        return $this->username;
    }

    /**
     * Set the value of username
     *
     * @return  self
     */
    public function setUsername(string $username): static
    {
        $this->username = $username;

        return $this;
    }

    /**
     * Get the value of token for Bearer authentication
     */
    public function getToken(): ?string {
        return $this->token;
    }

    /**
     * Set the value of token for Bearer authentication
     *
     * @return  self
     */
    public function setToken(string $token): static
    {
        $this->token = $token;

        return $this;
    }

    public function isSupported(): bool {
        return function_exists('fsockopen');
    }

    public function send(OutboundRequest $request): InboundResponse {
        if (! $this->isSupported()) {
            throw new ERuntimeError('Socket transport is not supported');
        }

        $url        = (string) $request->getUri();
        $headers    = new HeadersManager($request->getHeaders());
        $method     = strtoupper($request->getMethod());
        $bodyStream = $request->getBody();
        if ($bodyStream->isSeekable()) {
            $bodyStream->rewind();
        }
        $body = $bodyStream->getContents();

        if (! $urlParts = UrlHelper::parse($url)) {
            throw new EHttpException(501, sprintf('Malformed URL: %s', $url));
        }

        if (! isset($urlParts[3]) || empty($urlParts[3])) {
            if (($urlParts[1] == 'ssl' || $urlParts[1] == 'https') && extension_loaded('openssl')) {
                $urlParts[2] = "ssl://{$urlParts[2]}";
                $urlParts[3] = 443;
            } else {
                $urlParts[3] = 80;
            }
        }

        if (strtolower($urlParts[2]) === 'localhost') {
            $urlParts[2] = '127.0.0.1';
        }

        $errCode    = 0;
        $errMessage = '';
        if (($socket = fsockopen($urlParts[2], (int) $urlParts[3], $errCode, $errMessage, $this->getTimeout())) === false) {
            throw new EHttpException(501, $errMessage);
        }

        $requestPath = $urlParts[1] . '://' . $urlParts[2] . $urlParts[4] . (isset($urlParts[5]) && ! empty($urlParts[5]) ? $urlParts[5] : '') . (isset($urlParts[6]) ? "?{$urlParts[6]}" : '');

        if (empty($requestPath)) {
            $requestPath = '/';
        }

        $contentType      = $headers->getHeader('Content-Type', '');
        $isFormUrlEncoded = str_contains($contentType, 'application/x-www-form-urlencoded');
        $isJson           = str_contains($contentType, 'application/json') || JsonHelper::isJson($body);

        if (! $headers->hasHeader('Content-Type')) {
            if ($isJson) {
                $headers->setHeader('Content-Type', 'application/json');
            } elseif ($isFormUrlEncoded) {
                $headers->setHeader('Content-Type', 'application/x-www-form-urlencoded; charset=utf-8');
            }
        }
        $headers->setHeader('Content-Length', (string) mb_strlen($body));
        $headers->setHeader('Host', "$urlParts[2]:$urlParts[3]");
        $headers->setHeader('Connection', 'Close');

        if (! empty($authType = strtoupper($this->getAuthType() ?? ''))) {
            switch ($authType) {
            case 'BASIC':
                $user     = $this->getUsername() ?? 'username';
                $password = $this->getPassword() ?? 'password';

                $headers->setHeader('Authorization', sprintf('%s %s', $authType, base64_encode("$user:$password")));
                break;
            case 'BEARER':
            case 'OAUTH2':
            case 'JWT':
                $token = $this->getToken() ?? '';

                $headers->setHeader('Authorization', sprintf('%s %s', $authType, $token));
                break;
            }
        }

        $output = sprintf("%s %s HTTP/%.1f\r\n", $method, $requestPath, $request->getProtocolVersion());
        if ($headers->getCount() > 0) {
            $output .= implode("\r\n", $headers->getAsStrings()) . "\r\n";
        }
        $output .= "\r\n$body";

        fwrite($socket, $output);

        if ($request->isAsync() === true) {
            fclose($socket);
            return new InboundResponse();
        }

        // Read the first line of the headers (status line)
        $statusLine = fgets($socket);
        preg_match('#HTTP/\d+\.\d+ (\d+)#', $statusLine, $matches);
        $statusCode = isset($matches[1]) ? (int) $matches[1] : 500;

        // Read the headers
        $headers->clear();
        while (($line = fgets($socket)) !== false) {
            $line = rtrim($line, "\r\n");
            if ($line === "") {
                break; // Empty line means end of headers
            }

            if (strpos($line, ':') !== false) {
                [$name, $value] = explode(':', $line, 2);
                $headers->addHeader($name, $value);
            }
        }
        $responseHeaders = $headers->getAll();

        // Read the body of the response
        $responseBody = stream_get_contents($socket);
        fclose($socket);

        $stream = new Stream(fopen('php://temp', 'r+'));
        $stream->write($responseBody);
        $stream->rewind();

        $result = new InboundResponse($statusCode, $responseHeaders, $stream, $request->getProtocolVersion());

        // If the response is a redirect, we will create a new request instance and send it
        if ($method !== HttpHelper::METHOD_HEAD && ($location = $result->getHeaderLine('Location')) !== false) {
            $redirect = $this->getRedirectsCount();
            if (--$redirect > 0) {
                $this->setRedirectsCount($redirect);
                $request = $request->withUri(new Uri($location));
                return $this->send($request);
            } else {
                throw new EHttpException(500, 'Too many redirects.');
            }
        }

        return $result;
    }
}
/** End of SocketTrasport **/