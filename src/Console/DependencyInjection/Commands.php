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

namespace Scaleum\Console\DependencyInjection;

use Psr\Container\ContainerInterface;
use Scaleum\Console\CommandDispatcher;
use Scaleum\DependencyInjection\Container;
use Scaleum\DependencyInjection\Contracts\ConfiguratorInterface;
use Scaleum\DependencyInjection\Helpers\Autowire;

/**
 * Commands
 *
 * @author Maxim Kirichenko <kirichenko.maxim@gmail.com>
 */
class Commands implements ConfiguratorInterface {
    public function configure(Container $container): void {
        $container
            ->addDefinitions([
                CommandDispatcher::class => Autowire::create(),
                'commands.file'          => fn(ContainerInterface $c)          => $c->get('kernel.application_dir') . '/commands/default.php',
                'commands.directory'     => fn(ContainerInterface $c)     => $c->get('kernel.application_dir') . '/commands',
                'commands.dispatcher'    => CommandDispatcher::class,
            ]);
    }
}
/** End of Commands **/
