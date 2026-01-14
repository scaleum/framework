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

namespace Scaleum\Security\Services;

use Scaleum\Security\Supports\JwtTokenPayload;
use Scaleum\Stdlib\Base\Hydrator;
use Scaleum\Stdlib\Helpers\ArrayHelper;
use Scaleum\Stdlib\Helpers\HttpHelper;

/**
 * JwtManager
 *
 * @author Maxim Kirichenko <kirichenko.maxim@gmail.com>
 */
class JwtManager extends Hydrator {
    protected ?string $secret     = null;
    protected int $offset         = 300;
    protected ?string $last_error = null;

    protected ?string $issuer   = null; // iss
    protected ?string $audience = null; // aud

    /**
     * @var callable|null function(string $jti, array $payload): bool
     * Возвращает true если jti отозван (revoked).
     */
    protected $isJtiRevokedCallback = null;

    /**
     * @var callable|null function(string $jti, array $payload): void
     * Хук после успешной генерации — можно сохранить jti (allow-list / last-seen / etc.).
     */
    protected $onTokenIssuedCallback = null;

    private const ALG_HS256 = 'HS256';
    private const TYP_JWT   = 'JWT';

    private function base64UrlEncode(string $data): string {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    private function base64UrlDecode(string $data): string | false {
        $data   = strtr($data, '-_', '+/');
        $padLen = (4 - (strlen($data) % 4)) % 4;
        if ($padLen) {
            $data .= str_repeat('=', $padLen);
        }

        return base64_decode($data, true);
    }

    private function signHs256(string $data): string {
        $raw = hash_hmac('sha256', $data, $this->getSecret(), true);
        return $this->base64UrlEncode($raw);
    }

    private function generateJti(): string {
        return bin2hex(random_bytes(16));
    }

    /**
     * Генерация JWT (HS256) с iss/aud/jti.
     */
    public function generate(array $payload, ?int $expiry = null, ?string $issuer = null, ?string $audience = null): string {
        $now = time();
        $expiry ??= $now + $this->offset;

        $issuer ??= $this->issuer;
        $audience ??= $this->audience;

        $jti = $this->generateJti();

        $header = [
            'typ' => self::TYP_JWT,
            'alg' => self::ALG_HS256,
        ];

        $data = [
            'exp' => $expiry,
            'iat' => $now,
            'jti' => $jti,
            'iss' => (string) $issuer,
            'aud' => (string) $audience,
        ];

        $data = ArrayHelper::merge($data, $payload);

        $headerB64  = $this->base64UrlEncode((string) json_encode($header, JSON_UNESCAPED_SLASHES));
        $payloadB64 = $this->base64UrlEncode((string) json_encode($data, JSON_UNESCAPED_SLASHES));

        $signingInput = $headerB64 . '.' . $payloadB64;
        $signatureB64 = $this->signHs256($signingInput);

        $token = $signingInput . '.' . $signatureB64;

        if (is_callable($this->onTokenIssuedCallback)) {
            ($this->onTokenIssuedCallback)($jti, $data);
        }

        return $token;
    }

    /**
     * Верификация JWT (HS256) + iss/aud + jti.
     *
     * @param string|null $expectedIssuer Если null — берётся из $this->issuer
     * @param string|null $expectedAudience Если null — берётся из $this->audience
     */
    public function verify(string $token, ?string $expectedIssuer = null, ?string $expectedAudience = null): ?JwtTokenPayload {
        $this->last_error = null;

        $parts = explode('.', $token, 3);
        if (count($parts) !== 3 || $parts[0] === '' || $parts[1] === '' || $parts[2] === '') {
            $this->last_error = 'Token has invalid format';
            return null;
        }

        [$headerB64, $payloadB64, $signatureB64] = $parts;

        // header
        $headerJson = $this->base64UrlDecode($headerB64);
        if ($headerJson === false) {
            $this->last_error = 'Token header decode failed';
            return null;
        }

        $header = json_decode($headerJson, true);
        if (! is_array($header) || ($header['alg'] ?? null) !== self::ALG_HS256) {
            $this->last_error = 'Token has unsupported alg';
            return null;
        }

        // signature
        $signingInput = $headerB64 . '.' . $payloadB64;
        $expectedSig  = $this->signHs256($signingInput);

        if (! hash_equals($expectedSig, $signatureB64)) {
            $this->last_error = 'Token has invalid signature';
            return null;
        }

        // payload
        $payloadJson = $this->base64UrlDecode($payloadB64);
        if ($payloadJson === false) {
            $this->last_error = 'Token payload decode failed';
            return null;
        }

        $payload = json_decode($payloadJson, true);
        if (! is_array($payload) || ! isset($payload['exp'], $payload['jti'])) {
            $this->last_error = 'Token payload is invalid';
            return null;
        }

        $now = time();

        if (isset($payload['nbf']) && (int) $payload['nbf'] > $now) {
            $this->last_error = 'Token is not active yet';
            return null;
        }

        if ((int) $payload['exp'] < $now) {
            $this->last_error = 'Token has expired';
            return null;
        }

        // iss/aud checks
        $expectedIssuer ??= $this->issuer;
        $expectedAudience ??= $this->audience;

        if ($expectedIssuer !== null && $expectedIssuer !== '' && (($payload['iss'] ?? '') !== $expectedIssuer)) {
            $this->last_error = 'Token has invalid issuer';
            return null;
        }

        if ($expectedAudience !== null && $expectedAudience !== '' && (($payload['aud'] ?? '') !== $expectedAudience)) {
            $this->last_error = 'Token has invalid audience';
            return null;
        }

        // jti revoke check (опционально)
        $jti = (string) $payload['jti'];
        if ($jti === '') {
            $this->last_error = 'Token has invalid jti';
            return null;
        }

        if (is_callable($this->isJtiRevokedCallback)) {
            $isRevoked = (bool) ($this->isJtiRevokedCallback)($jti, $payload);
            if ($isRevoked) {
                $this->last_error = 'Token has been revoked';
                return null;
            }
        }

        return new JwtTokenPayload($payload);
    }

    public function setIssuer(?string $issuer): static {
        $this->issuer = $issuer;
        return $this;
    }

    public function getIssuer(): string {
        return $this->issuer ?? '';
    }

    public function setAudience(?string $audience): static {
        $this->audience = $audience;
        return $this;
    }

    public function getAudience(): string {
        return $this->audience ?? '';
    }

    public function setIsJtiRevokedCallback( ? callable $callback) : static {
        $this->isJtiRevokedCallback = $callback;
        return $this;
    }

    public function setOnTokenIssuedCallback( ? callable $callback) : static {
        $this->onTokenIssuedCallback = $callback;
        return $this;
    }

    public function getSecret(): string {
        if ($this->secret === null || $this->secret === '') {
            if (empty(getenv('HASH_HMAC_KEY'))) {
                putenv('HASH_HMAC_KEY=' . bin2hex(random_bytes(32)));
            }
            $this->secret = (string) getenv('HASH_HMAC_KEY');
        }

        return $this->secret;
    }

    public function setSecret(string $secret): static {
        $this->secret = $secret;
        return $this;
    }

    public function getOffset(): int {
        return $this->offset;
    }

    public function setOffset(int $offset): static {
        $this->offset = $offset;
        return $this;
    }

    public function getLastError(): string {
        return $this->last_error ?? '';
    }
}
