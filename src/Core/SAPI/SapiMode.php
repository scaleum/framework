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

namespace Scaleum\Core\SAPI;

use Scaleum\Stdlib\Exception\ERuntimeError;

/**
 * SapiMode
 *
 * @author Maxim Kirichenko <kirichenko.maxim@gmail.com>
 */
enum SapiMode: int {
case CONSOLE   = 1;
case HTTP      = 2;
case UNIVERSAL = 3;
case UNKNOWN   = 4;

    public const NAMES = [
        self::CONSOLE   => 'Console',
        self::HTTP      => 'Http',
        self::UNIVERSAL => 'Universal',
        self::UNKNOWN   => 'Unknown',
    ];

    public function getName(): string {
        return self::NAMES[$this->value];
    }

    public static function fromValue(int $value): self {
        return self::from($value);
    }

    public static function fromString(string $str): self {
        return match (strtolower($str)) {
            'console' => self::CONSOLE,
            'http' => self::HTTP,
            'universal' => self::UNIVERSAL,
            default => throw new ERuntimeError("Unknown SAPI mode: $str"),
        };
    }
}
/** End of SapiMode **/