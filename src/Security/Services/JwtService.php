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

use Avant\Http\Helpers\IpAddressHelper;
use Scaleum\Security\Supports\JwtTokenPayload;
use Scaleum\Stdlib\Base\Hydrator;

/**
 * JwtService
 *
 * @author Maxim Kirichenko <kirichenko.maxim@gmail.com>
 */
class JwtService extends Hydrator {
/**
 * @var string|null $secret The secret used for token generation and verification.
 */
    protected ?string $secret     = null;
    protected int $offset         = 300;
    protected ?string $last_error = null;

    /**
     * Encodes a token.
     *
     * @param mixed $token The token to encode.
     * @return string The encoded token.
     */
    private function encode($token) {
        return str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($token));
    }

    /**
     * Decodes a token.
     *
     * @param string $token The token to decode.
     * @return mixed The decoded token.
     */
    private function decode($token) {
        return base64_decode(str_replace(['-', '_'], ['+', '/'], $token));
    }

    /**
     * Generates a token for a user.
     *
     * @param int $user_id The ID of the user.
     * @param int|null $expiry The expiry time of the token (optional).
     * @return mixed The generated token(format "token.signature"), like 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJ1c2VyX2lkIjoxLCJleHBpcnkiOjE2MjIwNzYwMzA='.
     */
    public function generate(int $user_id, ?int $expiry = null, ?string $ip_address = null): mixed {
        if ($expiry === null) {
            $expiry = time() + $this->offset;
        }

        $payload = [
            'user_id'    => $user_id,
            'expiry'     => $expiry,
            'ip_address' => $ip_address ?? '',
        ];

        $token     = $this->encode(json_encode($payload));
        $signature = hash_hmac('sha256', $token, $this->getSecret());

        return $token . '.' . $signature;
    }

    /**
     * Verifies a token.
     *
     * @param string $token The token to verify.
     * @return ?JwtTokenPayload The result of the verification; the user ID if the token is valid, false otherwise.
     */
    public function verify(string $token): ?JwtTokenPayload {
        [$payload, $signature] = explode('.', $token, 2);

        if (hash_hmac('sha256', $payload, $this->getSecret()) !== $signature) {
            $this->last_error = "Token has invalid signature";
            return null;
        }

        $payload = json_decode($this->decode($payload), true);

        if ($payload['expiry'] < time()) {
            $this->last_error = "Token has expired";
            return null;
        }

        if (! empty($payload['ip_address']) && $payload['ip_address'] !== IpAddressHelper::getIpAddress()) {
            $this->last_error = "Token has invalid IP-address";
            return null;
        }

        return new JwtTokenPayload($payload);
    }

    /**
     * Get the value of secret
     */
    public function getSecret(): string {
        if ($this->secret === null) {
            if (empty(getenv('HASH_HMAC_KEY'))) {
                putenv('HASH_HMAC_KEY=' . bin2hex(random_bytes(32)));
            }

            $this->secret = getenv('HASH_HMAC_KEY');
        }

        return $this->secret;
    }

    /**
     * Set the value of secret
     *
     * @return  self
     */
    public function setSecret(string $secret): self {
        $this->secret = $secret;

        return $this;
    }

    /**
     * Get the value of offset
     */
    public function getOffset(): int {
        return $this->offset;
    }

    /**
     * Set the value of offset
     *
     * @return  self
     */
    public function setOffset(int $offset): self {
        $this->offset = $offset;

        return $this;
    }

    /**
     * Get the value of last_error
     */
    public function getLastError(): string {
        return $this->last_error ?? '';
    }
}
/** End of JwtService **/