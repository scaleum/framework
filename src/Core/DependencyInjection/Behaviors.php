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

namespace Scaleum\Core\DependencyInjection;

use Psr\Container\ContainerInterface;
use Scaleum\Core\Behaviors\Exceptions;
use Scaleum\Core\Behaviors\Kernel;
use Scaleum\DependencyInjection\Container;
use Scaleum\DependencyInjection\Contract\ConfiguratorInterface;

/**
 * Behaviors
 *
 * @author Maxim Kirichenko <kirichenko.maxim@gmail.com>
 */
class Behaviors implements ConfiguratorInterface {
    public function configure(Container $container): void {
        $container
            ->addDefinition(Kernel::class, function (ContainerInterface $container) {
                $result = new Kernel($container->get('kernel'));
                $result->register($container->get('event.manager'));
                return $result;
            })
            ->addDefinition(Exceptions::class, function (ContainerInterface $container) {
                $result = new Exceptions($container->get('kernel'));
                $result->register($container->get('event.manager'));
                return $result;
            });
    }
}
/** End of Behaviors **/