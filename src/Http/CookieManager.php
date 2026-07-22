<?php

declare(strict_types=1);
/**
 * This file is part of Scaleum Framework.
 *
 * (C) 2009-2025 Maxim Kirichenko <kirichenko.maxim@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Scaleum\Http;

use Scaleum\Stdlib\Base\Hydrator;
use Scaleum\Stdlib\Exceptions\EInvalidArgumentException;
use Scaleum\Stdlib\Helpers\JsonHelper;

class CookieManager extends Hydrator
{
    private const HASH_LEN = 32; // length of md5 hash

    protected bool $encode     = false;
    protected int $expire      = 3600;
    protected string $path     = '/';
    protected string $domain   = '';
    protected bool $secure     = false;
    protected bool $httpOnly   = false;
    protected string $sameSite = 'Lax';
    protected string $salt     = '7987a1d4c9cd4076b6d855f2d7c5fdb4';

    public function set(string $name, mixed $value, ?int $expire = null): bool
    {
        if (headers_sent()) {
            return false;
        }

        $names         = $this->resolveCookieNames($name);
        $preparedValue = $this->prepareForStorage($value);

        $success = $this->upsertCookieHeader(
            $name,
            $preparedValue,
            $expire ?? $this->getExpireTimestamp(),
            $this->getPath(),
            $this->getDomain(),
            $this->isSecure(),
            $this->isHttpOnly(),
            $this->getSameSite(),
        );

        if ($success) {
            foreach ($names as $key) {
                $_COOKIE[$key] = $preparedValue;
            }
        }

        return $success;
    }

    public function get(string $name, mixed $default = null): mixed
    {
        $key = $this->resolveRuntimeCookieKey($name);
        if ($key === null || ! isset($_COOKIE[$key])) {
            return $default;
        }

        return $this->restoreFromStorage($_COOKIE[$key]) ?? $default;
    }

    public function has(string $name): bool
    {
        $key = $this->resolveRuntimeCookieKey($name);

        return $key !== null && isset($_COOKIE[$key]);
    }

    public function delete(string $name): bool
    {
        if (headers_sent()) {
            return false;
        }

        $expires  = time() - 3600;
        $path     = $this->getPath();
        $domain   = $this->getDomain();
        $secure   = $this->isSecure();
        $httpOnly = $this->isHttpOnly();
        $sameSite = $this->getSameSite();
        $names    = $this->resolveCookieNames($name);
        $cookies  = [];

        foreach ($names as $key) {
            $cookies[] = [
                'name'     => $key,
                'value'    => '',
                'expires'  => $expires,
                'path'     => $path,
                'domain'   => $domain,
                'secure'   => $secure,
                'httpOnly' => $httpOnly,
                'sameSite' => $sameSite,
            ];
        }

        $success = $this->upsertCookieHeaders($cookies);
        if ($success) {
            foreach ($names as $key) {
                unset($_COOKIE[$key]);
            }
        }

        return $success;
    }

    public function setToResponse(OutboundResponse $response, string $name, mixed $value, ?int $expires = null, ?int $maxAge = null): void
    {
        $this->upsertResponseCookieHeaders($response, [
            [
                'name'     => $name,
                'value'    => $this->prepareForStorage($value),
                'expires'  => $expires,
                'maxAge'   => $maxAge,
                'path'     => $this->getPath(),
                'domain'   => $this->getDomain(),
                'secure'   => $this->isSecure(),
                'httpOnly' => $this->isHttpOnly(),
                'sameSite' => $this->getSameSite(),
            ],
        ]);
    }

    public function deleteFromResponse(OutboundResponse $response, string $name, string $value = ''): void
    {
        $expires  = time() - 3600;
        $path     = $this->getPath();
        $domain   = $this->getDomain();
        $secure   = $this->isSecure();
        $httpOnly = $this->isHttpOnly();
        $sameSite = $this->getSameSite();
        $cookies  = [];

        foreach ($this->resolveCookieNames($name) as $key) {
            $cookies[] = [
                'name'     => $key,
                'value'    => $value,
                'expires'  => $expires,
                'maxAge'   => 0,
                'path'     => $path,
                'domain'   => $domain,
                'secure'   => $secure,
                'httpOnly' => $httpOnly,
                'sameSite' => $sameSite,
            ];
        }

        $this->upsertResponseCookieHeaders($response, $cookies);
    }

    public function restore(string $value, mixed $default = null): mixed
    {
        return $this->restoreFromStorage($value) ?? $default;
    }

    protected function prepareForStorage(mixed $value): string
    {
        $value = ! is_scalar($value) ? json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : $value;

        if ($value === false) {
            $error = json_last_error_msg();
            throw new EInvalidArgumentException("Failed to encode value for cookie storage: {$error}");
        }

        if ($this->encode) {
            $hash  = md5("{$value}{$this->salt}");
            $value = "{$value}{$hash}";
            $value = rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
        }

        return $value;
    }

    protected function restoreFromStorage(string $value): mixed
    {
        if ($this->encode) {
            $value = base64_decode(str_pad(strtr($value, '-_', '+/'), strlen($value) % 4, '=', STR_PAD_RIGHT));
            if ($value === false) {
                return null; // Invalid base64 string
            }

            $hash  = substr($value, strlen($value) - self::HASH_LEN); // get last 32 chars
            $value = substr($value, 0, strlen($value) - self::HASH_LEN);

            // Does the md5 hash match?  This is to prevent manipulation of session data in user space
            if ($hash !== md5("$value{$this->salt}")) {
                return null;
            }
        }

        return JsonHelper::isJson($value) ? json_decode($value, true) : $value;
    }

    protected function upsertCookieHeader(string $name, string $value, int $expires, string $path, string $domain, bool $secure, bool $httpOnly, string $sameSite): bool
    {
        return $this->upsertCookieHeaders([
            [
                'name'     => $name,
                'value'    => $value,
                'expires'  => $expires,
                'path'     => $path,
                'domain'   => $domain,
                'secure'   => $secure,
                'httpOnly' => $httpOnly,
                'sameSite' => $sameSite,
            ],
        ]);
    }

    /**
     * @param array<int,array{name:string,value:string,expires:int,path:string,domain:string,secure:bool,httpOnly:bool,sameSite:string}> $cookies
     */
    protected function upsertCookieHeaders(array $cookies): bool
    {
        $headers       = headers_list();
        $rawCookieRows = [];

        foreach ($headers as $header) {
            if (stripos($header, 'Set-Cookie:') !== 0) {
                continue;
            }

            $rawCookieRows[] = trim(substr($header, strlen('Set-Cookie:')));
        }

        $deduplicated = [];
        $unparsed     = [];

        foreach ($rawCookieRows as $cookieHeader) {
            $cookieKey = $this->resolveCookieKeyFromHeader($cookieHeader);

            if ($cookieKey === null) {
                $unparsed[] = $cookieHeader;
                continue;
            }

            $deduplicated[$cookieKey] = $cookieHeader;
        }

        foreach ($cookies as $cookie) {
            $deduplicated[$this->buildCookieKey($cookie['name'], $cookie['path'], $cookie['domain'])] = $this->buildCookieHeader(
                $cookie['name'],
                $cookie['value'],
                $cookie['expires'],
                $cookie['path'],
                $cookie['domain'],
                $cookie['secure'],
                $cookie['httpOnly'],
                $cookie['sameSite'],
            );
        }

        header_remove('Set-Cookie');

        foreach ($unparsed as $cookieHeader) {
            header("Set-Cookie: {$cookieHeader}", false);
        }

        foreach ($deduplicated as $cookieHeader) {
            header("Set-Cookie: {$cookieHeader}", false);
        }

        return true;
    }

    /**
     * @param array<int,array{name:string,value:string,expires:?int,maxAge:?int,path:string,domain:string,secure:bool,httpOnly:bool,sameSite:string}> $cookies
     */
    protected function upsertResponseCookieHeaders(OutboundResponse $response, array $cookies): void
    {
        $deduplicated = [];
        $unparsed     = [];

        foreach ($response->getHeader('Set-Cookie') as $cookieHeader) {
            $cookieKey = $this->resolveCookieKeyFromHeader($cookieHeader);

            if ($cookieKey === null) {
                $unparsed[] = $cookieHeader;
                continue;
            }

            $deduplicated[$cookieKey] = $cookieHeader;
        }

        foreach ($cookies as $cookie) {
            $deduplicated[$this->buildCookieKey($cookie['name'], $cookie['path'], $cookie['domain'])] = $this->buildCookieHeader(
                $cookie['name'],
                $cookie['value'],
                $cookie['expires'],
                $cookie['path'],
                $cookie['domain'],
                $cookie['secure'],
                $cookie['httpOnly'],
                $cookie['sameSite'],
                $cookie['maxAge'],
            );
        }

        $response->setHeader('Set-Cookie', [...$unparsed, ...array_values($deduplicated)]);
    }

    protected function resolveCookieKeyFromHeader(string $cookieHeader): ?string
    {
        $parts = array_map('trim', explode(';', $cookieHeader));
        $first = array_shift($parts);

        if ($first === null || ! str_contains($first, '=')) {
            return null;
        }

        [$name] = explode('=', $first, 2);

        $path   = '';
        $domain = '';

        foreach ($parts as $part) {
            if ($part === '' || ! str_contains($part, '=')) {
                continue;
            }

            [$attributeName, $attributeValue] = explode('=', $part, 2);
            $attributeName                    = strtolower(trim($attributeName));
            $attributeValue                   = trim($attributeValue);

            if ($attributeName === 'path') {
                $path = $attributeValue;
            }

            if ($attributeName === 'domain') {
                $domain = $attributeValue;
            }
        }

        return $this->buildCookieKey($name, $path, $domain);
    }

    protected function buildCookieKey(string $name, string $path, string $domain): string
    {
        $normalizedName   = strtolower(trim($name));
        $normalizedPath   = strtolower(trim($path));
        $normalizedDomain = strtolower(trim($domain));

        return "{$normalizedName}|{$normalizedPath}|{$normalizedDomain}";
    }

    protected function buildCookieHeader(string $name, string $value, ?int $expires, string $path, string $domain, bool $secure, bool $httpOnly, string $sameSite, ?int $maxAge = null): string
    {
        $encodedValue = rawurlencode($value);
        $segments     = ["{$name}={$encodedValue}"];

        if ($expires !== null) {
            $expireDate = gmdate('D, d M Y H:i:s', $expires);
            $segments[] = "Expires={$expireDate} GMT";
        }

        if ($maxAge !== null) {
            $segments[] = "Max-Age={$maxAge}";
        }

        if ($path !== '') {
            $segments[] = "Path={$path}";
        }

        if ($domain !== '') {
            $segments[] = "Domain={$domain}";
        }

        if ($secure) {
            $segments[] = 'Secure';
        }

        if ($httpOnly) {
            $segments[] = 'HttpOnly';
        }

        if ($sameSite !== '') {
            $segments[] = "SameSite={$sameSite}";
        }

        return implode('; ', $segments);
    }

    public function setEncode(bool $encode): static
    {
        $this->encode = $encode;
        return $this;
    }

    public function isEncode(): bool
    {
        return $this->encode;
    }

    public function setExpire(int $expire): static
    {
        $this->expire = $expire;
        return $this;
    }

    public function getExpire(): int
    {
        return $this->expire;
    }

    public function getExpireTimestamp(): int
    {
        return time() + $this->getExpire();
    }

    public function setPath(string $path): static
    {
        $this->path = $path;
        return $this;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function setDomain(string $domain): static
    {
        $this->domain = $domain;
        return $this;
    }

    public function getDomain(): string
    {
        return $this->domain;
    }

    public function setSecure(bool $secure): static
    {
        $this->secure = $secure;
        return $this;
    }

    public function isSecure(): bool
    {
        return $this->secure;
    }

    public function setHttpOnly(bool $httpOnly): static
    {
        $this->httpOnly = $httpOnly;
        return $this;
    }

    public function isHttpOnly(): bool
    {
        return $this->httpOnly;
    }

    public function setSameSite(string $sameSite): static
    {
        $allowed = ['Strict', 'Lax', 'None'];
        if (! in_array($sameSite, $allowed, true)) {
            throw new EInvalidArgumentException(sprintf('Unacceptable SameSite value: %s. Allowed values: %s.', $sameSite, implode(', ', $allowed)));
        }

        $this->sameSite = $sameSite;
        return $this;
    }

    public function getSameSite(): string
    {
        return $this->sameSite;
    }

    public function getSalt(): string
    {
        return $this->salt;
    }

    public function setSalt(string $salt): static
    {
        $this->salt = $salt;
        return $this;
    }

    public function toArray(): array
    {
        return [
            'encode'   => $this->isEncode(),
            'expire'   => $this->getExpire(),
            'path'     => $this->getPath(),
            'domain'   => $this->getDomain(),
            'secure'   => $this->isSecure(),
            'httpOnly' => $this->isHttpOnly(),
            'sameSite' => $this->getSameSite(),
            'salt'     => $this->getSalt(),
        ];
    }

    private function normalizeIncomingName(string $name): string
    {
        return str_replace(['.', ' '], '_', $name);
    }

    private function resolveCookieNames(string $name): array
    {
        $normalizedName = $this->normalizeIncomingName($name);

        return $name === $normalizedName ? [$name] : [$name, $normalizedName];
    }

    private function resolveRuntimeCookieKey(string $name): ?string
    {
        foreach ($this->resolveCookieNames($name) as $key) {
            if (array_key_exists($key, $_COOKIE)) {
                return $key;
            }
        }

        return null;
    }
}
