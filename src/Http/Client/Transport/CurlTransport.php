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
 * CurlTransport
 *
 * @author Maxim Kirichenko <kirichenko.maxim@gmail.com>
 */
class CurlTransport extends TransportAbstract {
    protected ?string $authType = null;
    protected ?string $password = null;
    protected ?string $username = null;
    protected ?string $token    = null;
    protected ?string $domain   = null;

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

    public function send(OutboundRequest $request): InboundResponse {
        if (! $this->isSupported()) {
            throw new ERuntimeError('cURL is not available.');
        }

        $url     = (string) $request->getUri();
        $method  = strtoupper($request->getMethod());
        $headers = new HeadersManager($request->getHeaders());

        $bodyStream = $request->getBody();
        if ($bodyStream->isSeekable()) {
            $bodyStream->rewind(); // Гарантируем, что читаем с начала
        }
        $body = $bodyStream->getContents(); // Читаем данные потока

        if (! $urlParts = UrlHelper::parse($url)) {
            throw new EHttpException(501, sprintf('Malformed URL: %s', $url));
        }

        $handle = curl_init();
        curl_setopt_array($handle, [
            CURLOPT_FRESH_CONNECT  => true,
            CURLOPT_CONNECTTIMEOUT => $timeout = ceil(max($this->getTimeout(), 1)),
            CURLOPT_TIMEOUT        => $timeout,
            CURLOPT_URL            => $url,
            CURLOPT_REFERER        => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_MAXREDIRS      => max($this->getRedirectsCount(), 1),
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
        ]);

        $contentType      = $headers->getHeader('Content-Type', '');
        $isFormUrlEncoded = str_contains($contentType, 'application/x-www-form-urlencoded');
        $isJson           = str_contains($contentType, 'application/json') || JsonHelper::isJson($body);

        switch ($method) {
        case HttpHelper::METHOD_GET:
            if ($body != null) {
                if (JsonHelper::isJson($body)) {
                    curl_setopt_array($handle, [
                        CURLOPT_URL           => $url,
                        CURLOPT_POSTFIELDS    => $body,
                        CURLOPT_CUSTOMREQUEST => $method,
                    ]);

                    $headers->setHeader('Content-Type', 'application/json');
                    $headers->setHeader('Content-Length', (string) mb_strlen($body));
                } else {
                    curl_setopt($handle, CURLOPT_URL, sprintf("%s?%s", $url, $body));
                    $headers->setHeader('Content-Length', "0");
                }
            }
            break;
        case HttpHelper::METHOD_POST:
            curl_setopt($handle, CURLOPT_POST, true);
            curl_setopt($handle, CURLOPT_POSTFIELDS, $body);

            if (! $headers->hasHeader('Content-Type')) {
                if ($isJson) {
                    $headers->setHeader('Content-Type', 'application/json');
                } elseif ($isFormUrlEncoded) {
                    $headers->setHeader('Content-Type', 'application/x-www-form-urlencoded; charset=utf-8');
                }
            }
            $headers->setHeader('Content-Length', (string) mb_strlen($body));
            break;
        case HttpHelper::METHOD_HEAD:
            curl_setopt($handle, CURLOPT_CUSTOMREQUEST, $method);
            curl_setopt($handle, CURLOPT_NOBODY, true);
            break;
        default:
            curl_setopt($handle, CURLOPT_CUSTOMREQUEST, $method);

            if ($body != null) {
                curl_setopt($handle, CURLOPT_POSTFIELDS, $body);
            }

            if (! $headers->hasHeader('Content-Type')) {
                if ($isJson) {
                    $headers->setHeader('Content-Type', 'application/json');
                } elseif ($isFormUrlEncoded) {
                    $headers->setHeader('Content-Type', 'application/x-www-form-urlencoded; charset=utf-8');
                }
            }
            $headers->setHeader('Content-Length', (string) mb_strlen($body));
        }

        // $headers->setHeader('User-Agent', 'Scaleum Framework');
        // $headers->setHeader('Accept', '*/*');
        // $headers->setHeader('Accept-Encoding', 'gzip, deflate, br');
        $headers->setHeader('Host', $urlParts[2]);
        $headers->setHeader('Connection', 'Close');

        if (!empty($authType = strtoupper($this->getAuthType() ?? ''))) {
            curl_setopt($handle, CURLOPT_HTTPAUTH, defined($type = 'CURLAUTH_' . $authType) ? constant($type) : CURLAUTH_ANY);

            $user     = $this->getUsername() ?? 'username';
            $password = $this->getPassword() ?? 'password';

            switch ($authType) {
            case 'DIGEST':
            case 'BASIC':
            case 'ANY':
            case 'ANYSAFE':
                curl_setopt($handle, CURLOPT_USERPWD, "$user:$password");
                break;
            case 'BEARER':
                $token = $this->getToken() ?? '';
                curl_setopt($handle, CURLOPT_XOAUTH2_BEARER, $token);
                break;
            case 'NTLM':
                $domain = $this->getDomain() ?? 'DOMAIN';
                curl_setopt($handle, CURLOPT_USERPWD, "$domain\\$user:$password");
                break;
            }
        }

        curl_setopt($handle, CURLOPT_HEADER, (bool) $request->isAsync() !== true);

        // The option doesn't work with safe mode or when open_basedir is set.
        // Disable HEAD when making HEAD requests.
        $follow_location = false;
        if (! ini_get('safe_mode') && ! ini_get('open_basedir') && 'HEAD' != $method) {
            $follow_location = curl_setopt($handle, CURLOPT_FOLLOWLOCATION, true);
        }

        // Injections of headers
        if ($headers->getCount() > 0) {
            curl_setopt($handle, CURLOPT_HTTPHEADER, $headers->getAsStrings());
        }

        switch ($request->getProtocolVersion()) {
        case '1.1':
            curl_setopt($handle, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
            break;
        case '2.0':
            curl_setopt($handle, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_2);
            break;
        default:
            curl_setopt($handle, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
            break;
        }

        $responseBody    = curl_exec($handle);
        $responseHeaders = [];

        if ($request->isAsync() === true) {
            curl_close($handle);
            return new InboundResponse();
        }

        if ($curl_error = curl_error($handle)) {
            throw new EHttpException(500, $curl_error);
        }

        $statusCode = curl_getinfo($handle, CURLINFO_HTTP_CODE);

        if (! empty($responseBody)) {
            $headersSize = curl_getinfo($handle, CURLINFO_HEADER_SIZE);
            $headerLines = trim(substr($responseBody, 0, $headersSize));

            if (! empty($headerLines)) {
                if (strpos($headerLines, "\r\n\r\n") !== false) {
                    $headerParts = explode("\r\n\r\n", $headerLines);
                    $headerLines = $headerParts[count($headerParts) - 1];
                }

                // tolerate line terminator: CRLF = LF (RFC 2616 19.3)
                $headerLines = str_replace("\r\n", "\n", $headerLines);
                // unfold folded header fields. LWS = [CRLF] 1*( SP | HT ) <US-ASCII SP, space (32)>, <US-ASCII HT, horizontal-tab (9)> (RFC 2616 2.2)
                $headerLines = preg_replace('/\n[ \t]/', ' ', $headerLines);
                // create the headers array
                $headerLines = explode("\n", $headerLines);

                // Refill headers
                if (count($headerLines) > 0) {
                    $headers->clear();

                    foreach ($headerLines as $line) {
                        if (strpos($line, ':') !== false) {
                            [$name, $value] = explode(':', $line, 2);
                            $headers->addHeader($name, $value);
                        }
                    }
                    
                    $responseHeaders = $headers->getAll();
                }
            }

            if (strlen($responseBody) > $headersSize) {
                $responseBody = substr($responseBody, $headersSize);
            }
        }

        // Finish the request
        curl_close($handle);

        $stream = new Stream(fopen('php://temp', 'r+'));
        $stream->write($responseBody);
        $stream->rewind();

        $result = new InboundResponse($statusCode, $responseHeaders, $stream, $request->getProtocolVersion());

        // If the response is a redirect, we will create a new request instance and send it
        if ($follow_location !== true && ($location = $result->getHeaderLine('Location')) !== false) {
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

    public function isSupported(): bool {
        return function_exists('curl_init') && function_exists('curl_exec');
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

    /**
     * Get the value of domain for NTLM authentication
     */
    public function getDomain(): ?string {
        return $this->domain;
    }

    /**
     * Set the value of domain for NTLM authentication
     *
     * @return  self
     */
    public function setDomain(string $domain): static
    {
        $this->domain = $domain;

        return $this;
    }
}
/** End of CurlTransport **/