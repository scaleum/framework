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
            $_COOKIE[$name] = $preparedValue;
        }

        return $success;
    }

    public function get(string $name, mixed $default = null): mixed
    {
        if (! isset($_COOKIE[$name])) {
            return $default;
        }

        return $this->restoreFromStorage($_COOKIE[$name]) ?? $default;
    }

    public function has(string $name): bool
    {
        return isset($_COOKIE[$name]);
    }

    public function delete(string $name): bool
    {
        if (headers_sent()) {
            return false;
        }

        $success = $this->upsertCookieHeader(
            $name,
            '',
            time() - 3600,
            $this->getPath(),
            $this->getDomain(),
            $this->isSecure(),
            $this->isHttpOnly(),
            $this->getSameSite(),
        );

        if ($success) {
            unset($_COOKIE[$name]);
        }

        return $success;
    }

    protected function prepareForStorage(mixed $value): string
    {
        $value = ! is_scalar($value) ? json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : $value;

        if ($value === false) {
            throw new EInvalidArgumentException('Failed to encode value for cookie storage: ' . json_last_error_msg());
        }

        if ($this->encode) {
            $value = (string) $value . md5("$value{$this->salt}");
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

    protected function upsertCookieHeader(string $name,string $value,int $expires,string $path,string $domain,bool $secure,bool $httpOnly,string $sameSite): bool {
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

        $deduplicated[$this->buildCookieKey($name, $path, $domain)] = $this->buildCookieHeader($name,$value,$expires,$path,$domain,$secure,$httpOnly,$sameSite);

        header_remove('Set-Cookie');

        foreach ($unparsed as $cookieHeader) {
            header('Set-Cookie: ' . $cookieHeader, false);
        }

        foreach ($deduplicated as $cookieHeader) {
            header('Set-Cookie: ' . $cookieHeader, false);
        }

        return true;
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
        return strtolower(trim($name)) . '|' . strtolower(trim($path)) . '|' . strtolower(trim($domain));
    }

    protected function buildCookieHeader(string $name,string $value,int $expires,string $path,string $domain,bool $secure,bool $httpOnly,string $sameSite): string {
        $segments = [sprintf('%s=%s', $name, rawurlencode($value))];

        if ($expires > 0) {
            $segments[] = 'Expires=' . gmdate('D, d M Y H:i:s', $expires) . ' GMT';
        }

        if ($path !== '') {
            $segments[] = 'Path=' . $path;
        }

        if ($domain !== '') {
            $segments[] = 'Domain=' . $domain;
        }

        if ($secure) {
            $segments[] = 'Secure';
        }

        if ($httpOnly) {
            $segments[] = 'HttpOnly';
        }

        if ($sameSite !== '') {
            $segments[] = 'SameSite=' . $sameSite;
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

    public function setSalt($salt): static
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
}
