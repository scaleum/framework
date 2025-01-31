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

namespace Scaleum\Stdlib\Helpers;


/**
 * EnvHelper
 *
 * @author Maxim Kirichenko <kirichenko.maxim@gmail.com>
 */
class EnvHelper
{
    public static function get(string $key, mixed $default = null): mixed
    {
        return getenv($key) ?: $default;
    }

    public static function set(string $key, mixed $value): void
    {
        putenv(sprintf('%s=%s', $key, $value));
    }
}
/** End of EnvHelper **/