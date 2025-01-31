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

use Psr\Container\ContainerInterface;

/**
 * Factory
 *
 * @author Maxim Kirichenko <kirichenko.maxim@gmail.com>
 */
class EntryFactory {
    private \Closure $factory;

    public function __construct(\Closure $factory) {
        $this->factory = $factory;
    }

    public function resolve(ContainerInterface $container): mixed {
        return ($this->factory)($container);
    }
}
/** End of Factory **/