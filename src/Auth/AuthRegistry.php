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

namespace Scaleum\Auth;

use Scaleum\Auth\Contracts\AuthenticatorInterface;

/**
 * AuthRegistry
 *
 * @author Maxim Kirichenko <kirichenko.maxim@gmail.com>
 */
class AuthRegistry
{
    private static array $factories = [];

    public static function register(callable $factory): void
    {
        self::$factories[] = $factory;
    }

    /** @return AuthenticatorInterface[] */
    public static function resolveAll(): array
    {
        return array_map(fn($factory) => $factory(), self::$factories);
    }
}
/** End of AuthRegistry **/