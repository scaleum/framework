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

namespace Scaleum\Session\Channels;

use Scaleum\Http\InboundRequest;
use Scaleum\Http\OutboundResponse;
use Scaleum\Session\Contracts\SessionChannelInterface;
use Scaleum\Stdlib\Base\Hydrator;
use Scaleum\Stdlib\Exceptions\EInvalidArgumentException;
use Scaleum\Stdlib\Helpers\JsonHelper;

/**
 * CookieSessionChannel
 *
 * @author Maxim Kirichenko <kirichenko.maxim@gmail.com>
 */
class CookieChannel extends Hydrator implements SessionChannelInterface {
    private const HASH_LEN     = 32;
    protected string $keyName  = 'SID';
    protected string $path     = '/';
    protected string $domain   = '';
    protected bool $secure     = false;
    protected bool $httpOnly   = false;
    protected string $sameSite = 'Lax';
    protected bool $encode     = false;
    protected string $secret     = '771dc153d1d74684b252ecde98a9b6f1';
    public function fetchFromRequest(InboundRequest $request): ?string {
        $cookies = $request->getCookieParams();
        if (! empty($cookies[$this->keyName])) {
            return $this->decode(rawurldecode((string) $cookies[$this->keyName]));
        }

        return null;
    }

    public function writeToResponse(OutboundResponse $response, string $id, ?int $ttl = null): void {
        $key   = rawurlencode($this->keyName);
        $value = $this->encode(rawurlencode($id));

        $attributes = [
            'Path'     => $this->getPath(),
            'Domain'   => $this->getDomain(),
            'Secure'   => $this->isSecure(),
            'HttpOnly' => $this->isHttpOnly(),
            'SameSite' => $this->getSameSite(),
        ];

        if ($ttl > 0) {
            $expires               = gmdate('D, d M Y H:i:s', time() + $ttl) . ' GMT';
            $attributes['Expires'] = $expires;
            $attributes['Max-Age'] = (string) $ttl;
        }

        $parts = ["{$key}={$value}"];
        foreach ($attributes as $attrName => $attrValue) {
            if (is_bool($attrValue)) {
                if ($attrValue) {
                    $parts[] = $attrName;
                }
            } elseif ($attrValue !== '') {
                $parts[] = "{$attrName}={$attrValue}";
            }
        }

        $response->addHeader('Set-Cookie', implode('; ', $parts));
    }

    public function clearInResponse(OutboundResponse $response): void {
        $cookie = sprintf(
            '%s=deleted; Path=/; Expires=Thu, 01 Jan 1970 00:00:00 GMT; Path=/',
            rawurlencode($this->keyName)
        );

        $response->addHeader('Set-Cookie', $cookie);
    }

    public function setPath(string $path): static {
        $this->path = $path;
        return $this;
    }

    public function getPath(): string {
        return $this->path;
    }

    public function setDomain(string $domain): static {
        $this->domain = $domain;
        return $this;
    }

    public function getDomain(): string {
        return $this->domain;
    }

    public function setSecure(bool $secure): static {
        $this->secure = $secure;
        return $this;
    }

    public function isSecure(): bool {
        return $this->secure;
    }

    public function setHttpOnly(bool $httpOnly): static {
        $this->httpOnly = $httpOnly;
        return $this;
    }

    public function isHttpOnly(): bool {
        return $this->httpOnly;
    }

    public function setSameSite(string $sameSite): static {
        $allowed = ['Strict', 'Lax', 'None'];
        if (! in_array($sameSite, $allowed, true)) {
            throw new EInvalidArgumentException(sprintf('Unacceptable SameSite value: %s. Allowed values: %s.', $sameSite, implode(', ', $allowed)));
        }

        $this->sameSite = $sameSite;
        if ($sameSite === 'None' && ! $this->isSecure()) {
            $this->setSecure(true);
        }

        return $this;
    }

    public function getSameSite(): string {
        return $this->sameSite;
    }

    public function getSecret(): string {
        return $this->secret;
    }

    public function setSecret(string $secret): static
    {
        $this->secret = $secret;
        return $this;
    }

    public function setEncode(bool $encode): static {
        $this->encode = $encode;
        return $this;
    }

    public function isEncode(): bool {
        return $this->encode;
    }

    protected function encode(mixed $value): string {
        $value = ! is_scalar($value) ? json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : $value;

        if ($value === false) {
            throw new EInvalidArgumentException('Failed to encode value for cookie storage: ' . json_last_error_msg());
        }

        if ($this->encode) {
            $value = (string) $value . md5("$value{$this->getSecret()}");
            $value = rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
        }

        return $value;
    }

    protected function decode(string $value): mixed {
        if ($this->encode) {
            $value = base64_decode(str_pad(strtr($value, '-_', '+/'), strlen($value) % 4, '=', STR_PAD_RIGHT));
            if ($value === false) {
                return null; // Invalid base64 string
            }

            $hash  = substr($value, strlen($value) - self::HASH_LEN); // get last 32 chars
            $value = substr($value, 0, strlen($value) - self::HASH_LEN);

            // Does the md5 hash match?  This is to prevent manipulation of session data in user space
            if ($hash !== md5("$value{$this->getSecret()}")) {
                return null;
            }
        }

        return JsonHelper::isJson($value) ? json_decode($value, true) : $value;
    }
}
/** End of CookieSessionChannel **/