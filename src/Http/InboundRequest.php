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
 * InboundRequest - Incoming request to our server
 *
 * @author Maxim Kirichenko <kirichenko.maxim@gmail.com>
 */
class InboundRequest extends Message implements ServerRequestInterface {
    protected mixed $parsedBody   = null;
    protected ?string $userAgent  = null;
    protected array $attributes   = [];
    protected array $cookieParams = [];
    protected array $files        = [];
    protected array $queryParams  = [];
    protected array $serverParams = [];
    protected string $method;
    protected UriInterface $uri;
    protected ?string $requestTarget = null;

    public function __construct(
        string $method,
        UriInterface $uri,
        array $serverParams = [],
        array $headers = [],
        ?StreamInterface $body = null,
        array $queryParams = [],
        ?array $parsedBody = null,
        array $cookieParams = [],
        array $files = [],
        string $protocol = '1.1'
    ) {
        parent::__construct($headers, $body, $protocol);

        $this->cookieParams = $cookieParams;
        $this->files        = $files;
        $this->method       = strtoupper($method);
        $this->parsedBody   = $parsedBody;
        $this->queryParams  = $queryParams;
        $this->serverParams = $serverParams;
        $this->uri          = $uri;

        if ($this->parsedBody === null) {
            $this->parsedBody = $this->parseBody($body, $this->getContentType(), strtoupper($method));
        }
    }

    protected function parseBody(?StreamInterface $body, string $contentType, string $method): mixed {

        // Если `application/x-www-form-urlencoded` или `multipart/form-data`
        if ($method === HttpHelper::METHOD_POST && str_contains($contentType, 'application/x-www-form-urlencoded')) {
            return $_POST; // Данные уже распарсены PHP
        }

        if ($method === HttpHelper::METHOD_POST && str_contains($contentType, 'multipart/form-data')) {
            return array_merge($_POST, $this->normalizeFiles($_FILES)); // Форма с файлами
        }

        // Если тело запроса не передано, читаем `php://input`
        $rawBody = (string) ($body ?? new Stream(fopen('php://input', 'r+')));

        // Если `application/json`
        if (str_contains($contentType, 'application/json')) {
            return json_decode($rawBody, true) ?? null;
        }

        // Если `application/x-www-form-urlencoded` (PUT, PATCH, DELETE)
        if (str_contains($contentType, 'application/x-www-form-urlencoded')) {
            parse_str($rawBody, $parsed);
            return $parsed;
        }

        // Если `application/xml`, `text/plain`, `application/custom`
        if (str_contains($contentType, 'application/xml') || str_contains($contentType, 'text/plain')) {
            return $rawBody; // Просто строка
        }

        return null; // Неизвестный формат
    }

    protected function normalizeFiles(array $files): array {
        $normalized = [];

        foreach ($files as $key => $file) {
            // Loaded one file (without multiple)
            if (! is_array($file['name'])) {
                $normalized[$key] = $file;
                continue;
            }

            // Loaded multiple files
            $fileCount = count($file['name']);
            for ($i = 0; $i < $fileCount; $i++) {
                $normalized[$key][$i] = [
                    'name'     => $file['name'][$i],
                    'type'     => $file['type'][$i],
                    'tmp_name' => $file['tmp_name'][$i],
                    'error'    => $file['error'][$i],
                    'size'     => $file['size'][$i],
                ];
            }
        }

        return $normalized;
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
                $_COOKIE[static::cleanKey((string) $key)] = static::cleanData($val);
            }
        }

        // Clean $_GET Data
        if (is_array($_GET) and count($_GET) > 0) {
            foreach ($_GET as $key => $val) {
                $_GET[static::cleanKey((string) $key)] = static::cleanData($val);
            }
        }

        // Clean $_POST Data
        if (is_array($_POST) and count($_POST) > 0) {
            foreach ($_POST as $key => $val) {
                $_POST[static::cleanKey((string) $key)] = static::cleanData($val);
            }
        }

        // Sanitize PHP_SELF
        $_SERVER['PHP_SELF'] = strip_tags($_SERVER['PHP_SELF']);
    }

    protected static function cleanData(array | string $str) {
        if (is_array($str)) {
            $array = [];
            foreach ($str as $key => $val) {
                $array[static::cleanKey((string) $key)] = static::cleanData($val);
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
        $headers = new HeadersManager();

        if (function_exists('getallheaders')) {
            $headers->setHeaders(getallheaders());
            return $headers->getAll();
        }

        foreach ($_SERVER as $name => $value) {
            if (str_starts_with($name, 'HTTP_')) {
                $header = substr($name, 5);
                $header = str_replace(['_', '-'], ' ', strtolower($header));
                $header = str_replace(' ', '-', ucwords($header));

                $headers->addHeader($header, $value);
            }
        }

        return $headers->getAll();
    }

    public function getInputParams(): array {
        $method = strtoupper($this->getMethod());

        // Only for GET, HEAD, OPTIONS
        if ($method === HttpHelper::METHOD_GET || $method === HttpHelper::METHOD_HEAD || $method === HttpHelper::METHOD_OPTIONS) {
            return $this->queryParams;
        }

        // Only for POST, PUT, PATCH, DELETE
        return is_array($this->parsedBody) ? $this->parsedBody : [];
    }

    public function getInputParam(string $param, mixed $default = null): mixed {
        $params = $this->getInputParams();
        return $params[$param] ?? $default;
    }

    public static function fromGlobals(): self {
        self::sanitize();

        return new self(
            $_SERVER['REQUEST_METHOD'] ?? HttpHelper::METHOD_GET,
            new Uri(),
            $_SERVER,
            self::getHeadersFromGlobals(),
            new Stream(fopen('php://input', 'r+')),
            $_GET,
            $_POST ?: null,
            $_COOKIE,
            $_FILES
        );
    }

    public function getUserAgent(): ?string {
        if ($this->userAgent === null) {
            $this->userAgent = HttpHelper::getUserAgent();
        }

        return $this->userAgent;
    }

    public function getUserIP(): string {
        return $this->serverParams['REMOTE_ADDR'] ?? HttpHelper::getUserIP();
    }

    public function getContentType(): string {
        $type = strtolower($this->serverParams['CONTENT_TYPE'] ?? $this->getHeaderLine('Content-type'));
        if (strpos($type, ',')) {
            $type = current(explode(',', $type));
        }
        return $type;
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
        return $this->parsedBody;
    }

    public function withParsedBody($data): static
    {
        $clone             = clone $this;
        $clone->parsedBody = $data;
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

    public function getRequestTarget(): string {
        return $this->requestTarget ?: (string) $this->uri;
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
        $clone->method = strtoupper($method);
        return $clone;
    }

    public function getUri(): UriInterface {
        return $this->uri;
    }

    public function withUri(UriInterface $uri, $preserveHost = false): static
    {
        $clone      = clone $this;
        $clone->uri = $uri;

        if (! $preserveHost || ! $this->hasHeader('Host')) {
            $clone = $clone->withHeader('Host', $uri->getHost());
        }

        return $clone;
    }
}
/** End of InboundRequest **/
