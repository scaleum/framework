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

use DI\ContainerBuilder;
use Scaleum\Core\Behaviors\Exceptions;
use Scaleum\Core\ContainerConfiguratorInterface;
use Scaleum\Core\Behaviors\KernelBehaviors;

/**
 * Behaviors
 *
 * @author Maxim Kirichenko <kirichenko.maxim@gmail.com>
 */
class Behaviors implements ContainerConfiguratorInterface {
    public function configure(ContainerBuilder $builder): void {
        $builder->addDefinitions([
            KernelBehaviors::class => \DI\autowire()
                ->constructor(\DI\get('kernel'))
                ->method('register', \DI\get('event-manager')),
            Exceptions::class => \DI\autowire()
                ->constructor(\DI\get('kernel'))
                ->method('register', \DI\get('event-manager')),

            # aliases behaviors
            'behavior::kernel'         => \DI\get(KernelBehaviors::class),
            'behavior::exceptions'     => \DI\get(Exceptions::class),
            # behaviors
            'behaviors'                => \DI\add([
                'behavior::kernel',
                'behavior::exceptions',
            ]),
        ]);
    }
}
/** End of Behaviors **/