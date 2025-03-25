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

namespace Scaleum\Security\Supports;

/**
 * TokenResolver
 *
 * @author Maxim Kirichenko <kirichenko.maxim@gmail.com>
 */
class TokenResolver {
    public function __construct(private array $tokenKeys = ['token', 'api_token', 'api_key']) {}

    public function resolve(array $get, array $post, array $headers, array $cookies = []): ?string {
        foreach ($this->tokenKeys as $key) {
            if (isset($get[$key])) {
                return trim($get[$key]);
            }

            if (isset($post[$key])) {
                return trim($post[$key]);
            }

            foreach ($headers as $headerKey => $value) {
                $normalizedKey = strtolower($headerKey);

                if (in_array($normalizedKey,
                    [
                        strtolower($key),
                        'x-' . strtolower($key),
                        'x_' . strtolower($key),
                        str_replace('_', '-', strtolower($key)),
                        'x-' . str_replace('_', '-', strtolower($key)),
                    ],
                    true)
                ) {
                    return trim($value);
                }
            }
            if (isset($cookies[$key])) {
                return trim($cookies[$key]);
            }
        }

        // Support Authorization: Bearer <token>
        if (isset($headers['Authorization']) && preg_match('/Bearer\s+(.+)/i', $headers['Authorization'], $matches)) {
            return trim($matches[1]);
        }

        return null;
    }


    public static function fromServer(array $server): array
    {
        $headers = [];

        foreach ($server as $key => $value) {
            if (str_starts_with($key, 'HTTP_')) {
                $name = strtolower(str_replace('_', '-', substr($key, 5)));
                $headers[$name] = $value;
            } elseif (in_array($key, ['CONTENT_TYPE', 'CONTENT_LENGTH'], true)) {
                $name = strtolower(str_replace('_', '-', $key));
                $headers[$name] = $value;
            }
        }

        return $headers;
    }

    public static function isServerHeaders(array $headers): bool
    {
        foreach (array_keys($headers) as $key) {
            if (str_starts_with($key, 'HTTP_')) {
                return true;
            }
        }
        return false;
    }    
}
/** End of TokenResolver **/