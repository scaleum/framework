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

namespace Scaleum\Core;


/**
 * Version
 *
 * @author Maxim Kirichenko <kirichenko.maxim@gmail.com>
 */
final class Version
{
    public const VERSION = '1.0.3';

    public static function get(): string
    {
        return static::VERSION;
    }
}
/** End of Version **/