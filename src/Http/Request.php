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

namespace Scaleum\Http;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UriInterface;
use Scaleum\Stdlib\Exceptions\ERuntimeError;
use Scaleum\Stdlib\Helpers\HttpHelper;
use Scaleum\Stdlib\Helpers\StringHelper;
use Scaleum\Stdlib\Helpers\Utf8Helper;

/**
 * Request
 *
 * @author Maxim Kirichenko <kirichenko.maxim@gmail.com>
 */
class Request implements ServerRequestInterface {
    private ?array $bodyParsed = null;
    private ?string $userAgent = null;
    private StreamInterface $body;
    private UriInterface $uri;
    private array $attributes;
    private array $cookieParams;
    private array $files;
    private array $headers;
    private array $queryParams;
    private array $serverParams;
    private string $method;

    public function __construct(
        string $method,
        UriInterface $uri,
        array $serverParams = [],
        array $headers = [],
        ?StreamInterface $body = null,
        array $queryParams = [],
        ?array $bodyParsed = null,
        array $cookieParams = [],
        array $files = []
    ) {
        $this->attributes   = [];
        $this->body         = $body ?? new Stream(fopen('php://input', 'r+'));
        $this->bodyParsed   = $bodyParsed;
        $this->cookieParams = $cookieParams;
        $this->files        = $files;
        $this->headers      = $headers;
        $this->method       = $method;
        $this->queryParams  = $queryParams;
        $this->serverParams = $serverParams;
        $this->uri          = $uri;

        if ($bodyParsed === null) {
            $contentType = $this->getContentType();
            $raw         = (string) $this->body;

            if ($method === HttpHelper::METHOD_POST && $_POST) {
                $this->bodyParsed = $_POST; // just POST
            } elseif (in_array($method, [HttpHelper::METHOD_PUT, HttpHelper::METHOD_PATCH, HttpHelper::METHOD_DELETE])) {
                if (str_contains($contentType, 'application/json')) {
                    $this->bodyParsed = json_decode($raw, true) ?? null;
                } elseif (str_contains($contentType, 'application/x-www-form-urlencoded')) {
                    parse_str($raw, $parsed);
                    $this->bodyParsed = $parsed;
                } else {
                    $this->bodyParsed = null; // if unknown content type
                }
            } else {
                $this->bodyParsed = null;
            }
        }

    }

    public static function fromGlobals(): self {
        self::sanitize();

        return new self(
            $_SERVER['REQUEST_METHOD'] ?? HttpHelper::METHOD_GET,
            new Uri(),
            $_SERVER,
            self::getHeadersFromGlobals(),
            new Stream(fopen('php://input', 'r')),
            $_GET,
            null,
            $_COOKIE,
            $_FILES
        );
    }

    protected static function sanitize() {
        // Clean $_COOKIE Data
        if (is_array($_COOKIE) and count($_COOKIE) > 0) {
            // Also get rid of specially treated cookies that might be set by a server
            // or silly application, that are of no use to a application anyway
            // but that when present will trip our 'Disallowed Key Characters' alarm
            // http://www.ietf.org/rfc/rfc2109.txt
            // note that the key names below are single quoted strings, and are not PHP variables
            unset($_COOKIE['$Version']);
            unset($_COOKIE['$Path']);
            unset($_COOKIE['$Domain']);

            foreach ($_COOKIE as $key => $val) {
                $_COOKIE[static::cleanKey($key)] = static::cleanData($val);
            }
        }

        // Clean $_GET Data
        if (is_array($_GET) and count($_GET) > 0) {
            foreach ($_GET as $key => $val) {
                $_GET[static::cleanKey($key)] = static::cleanData($val);
            }
        }

        // Clean $_POST Data
        if (is_array($_POST) and count($_POST) > 0) {
            foreach ($_POST as $key => $val) {
                $_POST[static::cleanKey($key)] = static::cleanData($val);
            }
        }

        // Sanitize PHP_SELF
        $_SERVER['PHP_SELF'] = strip_tags($_SERVER['PHP_SELF']);
    }

    protected static function cleanData(array | string $str) {
        if (is_array($str)) {
            $array = [];
            foreach ($str as $key => $val) {
                $array[static::cleanKey($key)] = static::cleanData($val);
            }

            return $array;
        }

        // Clean UTF-8 if supported
        if (Utf8Helper::isUtf8Enabled()) {
            $str = Utf8Helper::clean($str, '');
            $str = Utf8Helper::cleanUtf8Bom($str);
        }

        // Remove control characters
        $str = StringHelper::cleanInvisibleChars($str);

        // Standardize newlines if needed
        if (! (defined('PHP_EOL') && PHP_EOL == "\r\n")) {
            if (strpos($str, "\r") !== false) {
                $str = str_replace(["\r\n", "\r", "\r\n\n"], PHP_EOL, $str);
            }
        }

        return $str;
    }

    protected static function cleanKey(string $str): string {
        if (! preg_match('/^[a-z0-9\:_\/\-]+$/i', $str)) {
            throw new ERuntimeError('Disallowed Key Characters');
        }

        // Clean UTF-8 if supported
        if (Utf8Helper::isUtf8Enabled()) {
            $str = Utf8Helper::clean($str, '');
            $str = Utf8Helper::cleanUtf8Bom($str);
        }

        return $str;
    }

    protected static function getHeadersFromGlobals(): array {
        if (function_exists('getallheaders')) {
            return getallheaders();
        }

        $headers = [];
        foreach ($_SERVER as $name => $value) {
            if (str_starts_with($name, 'HTTP_')) {
                $header = substr($name, 5);
                $header = str_replace(['_', '-'], ' ', strtolower($header));
                $header = str_replace(' ', '-', ucwords($header));

                $headers[$header] = $value;
            }
        }

        return $headers;
    }

    public function getUserAgent(): ?string {
        if ($this->userAgent === null) {
            $headers = [
                // The default User-Agent string.
                'HTTP_USER_AGENT',
                // Header can occur on devices using Opera Mini.
                'HTTP_X_OPERAMINI_PHONE_UA',
                // Vodafone specific header: http://www.seoprinciple.com/mobile-web-community-still-angry-at-vodafone/24/
                'HTTP_X_DEVICE_USER_AGENT',
                'HTTP_X_ORIGINAL_USER_AGENT',
                'HTTP_X_SKYFIRE_PHONE',
                'HTTP_X_BOLT_PHONE_UA',
                'HTTP_DEVICE_STOCK_UA',
                'HTTP_X_UCBROWSER_DEVICE_UA',
            ];

            $str = '';
            foreach ($headers as $header) {
                if (isset($this->serverParams[$header]) && ! empty($this->serverParams[$header])) {
                    $str .= $this->serverParams[$header] . ' ';
                }
            }

            $this->userAgent = trim(empty($str) ? 'Unknown' : substr($str, 0, 512));
        }

        return $this->userAgent;
    }

    public function getUserIP(): string {
        return $this->serverParams['REMOTE_ADDR'] ?? '0.0.0.0';
    }

    public function getContentType(): string {
        $type = strtolower($this->serverParams['CONTENT_TYPE'] ?? ($this->headers['Content-type'] ?? 'text/html'));
        if (strpos($type, ',')) {
            $type = current(explode(',', $type));
        }
        return $type;
    }

    public function getProtocolVersion(): string {
        return $this->serverParams['SERVER_PROTOCOL'] ?? '1.1';
    }

    public function withProtocolVersion($version): static
    {
        $clone                                  = clone $this;
        $clone->serverParams['SERVER_PROTOCOL'] = $version;
        return $clone;
    }

    public function getHeaders(): array {
        return $this->headers;
    }

    public function hasHeader($name): bool {
        return isset($this->headers[$name]);
    }

    public function getHeader($name): array {
        return $this->headers[$name] ?? [];
    }

    public function getHeaderLine($name): string {
        return implode(', ', $this->getHeader($name));
    }

    public function withHeader($name, $value): static
    {
        $clone                 = clone $this;
        $clone->headers[$name] = is_array($value) ? $value : [$value];
        return $clone;
    }

    public function withAddedHeader($name, $value): static
    {
        $clone                 = clone $this;
        $clone->headers[$name] = array_merge($this->headers[$name] ?? [], (array) $value);
        return $clone;
    }

    public function withoutHeader($name): static
    {
        $clone = clone $this;
        unset($clone->headers[$name]);
        return $clone;
    }

    public function getBody(): StreamInterface {
        return $this->body;
    }

    public function withBody(StreamInterface $body): static
    {
        $clone       = clone $this;
        $clone->body = $body;
        return $clone;
    }

    public function getRequestTarget(): string {
        return $this->uri->getPath() . ($this->uri->getQuery() ? '?' . $this->uri->getQuery() : '');
    }

    public function withRequestTarget($requestTarget): static
    {
        $clone      = clone $this;
        $clone->uri = $clone->uri->withPath($requestTarget);
        return $clone;
    }

    public function getMethod(): string {
        return $this->method;
    }

    public function withMethod($method): static
    {
        $clone         = clone $this;
        $clone->method = $method;
        return $clone;
    }

    public function getUri(): UriInterface {
        return $this->uri;
    }

    public function withUri(UriInterface $uri, $preserveHost = false): static
    {
        $clone      = clone $this;
        $clone->uri = $uri;
        return $clone;
    }

    public function getServerParams(): array {
        return $this->serverParams;
    }

    public function getCookieParams(): array {
        return $this->cookieParams;
    }

    public function withCookieParams(array $cookies): static
    {
        $clone               = clone $this;
        $clone->cookieParams = $cookies;
        return $clone;
    }

    public function getQueryParams(): array {
        return $this->queryParams;
    }

    public function withQueryParams(array $query): static
    {
        $clone              = clone $this;
        $clone->queryParams = $query;
        return $clone;
    }

    public function getUploadedFiles(): array {
        return $this->files;
    }

    public function withUploadedFiles(array $uploadedFiles): static
    {
        $clone        = clone $this;
        $clone->files = $uploadedFiles;
        return $clone;
    }

    public function getParsedBody(): mixed {
        return $this->bodyParsed;
    }

    public function withParsedBody($data): static
    {
        $clone             = clone $this;
        $clone->bodyParsed = $data;
        return $clone;
    }

    public function getAttributes(): array {
        return $this->attributes;
    }

    public function getAttribute($name, $default = null): mixed {
        return $this->attributes[$name] ?? $default;
    }

    public function withAttribute($name, $value): static
    {
        $clone                    = clone $this;
        $clone->attributes[$name] = $value;
        return $clone;
    }

    public function withoutAttribute($name): static
    {
        $clone = clone $this;
        unset($clone->attributes[$name]);
        return $clone;
    }
}
/** End of Request **/
