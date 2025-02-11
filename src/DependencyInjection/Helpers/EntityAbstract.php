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

namespace Scaleum\DependencyInjection\Helpers;

use Scaleum\DependencyInjection\Contracts\EntityInterface;

/**
 * EntityAbstract
 *
 * @author Maxim Kirichenko <kirichenko.maxim@gmail.com>
 */
abstract class EntityAbstract implements EntityInterface {
    public static function create(...$args) {
        return new static(...$args);
    }
}
/** End of EntityAbstract **/