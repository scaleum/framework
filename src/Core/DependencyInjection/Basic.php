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
use Scaleum\Config\LoaderResolver;
use Scaleum\DependencyInjection\Container;
use Scaleum\DependencyInjection\Contracts\ConfiguratorInterface;
use Scaleum\Core\KernelInterface;
use Scaleum\Events\EventManager;
use Scaleum\Logger\LoggerManager;
use Scaleum\Services\ServiceManager;
use Scaleum\Stdlib\SAPI\Explorer;

/**
 * Framework
 *
 * @author Maxim Kirichenko <kirichenko.maxim@gmail.com>
 */
class Basic implements ConfiguratorInterface {
    public function configure(Container $container): void {
        $container->addDefinitions([
            'kernel.version'       => '1.0.0',
            'kernel.sapi_type'     => Explorer::getType(),
            'kernel.sapi_family'   => Explorer::getTypeFamily(),
            KernelInterface::class => function (ContainerInterface $c) {
                return $c->get('kernel');
            },
            EventManager::class    => function (ContainerInterface $c) {
                return new EventManager();
            },
            'event.manager'        => EventManager::class,
            ServiceManager::class  => function (ContainerInterface $c) {
                return new ServiceManager();
            },
            'service.manager'      => ServiceManager::class,
            LoggerManager::class   => function (ContainerInterface $c) {
                return new LoggerManager();
            },
            'log.manager'          => LoggerManager::class,
            LoaderResolver::class  => function (ContainerInterface $c) {
                return new LoaderResolver($c->get('environment'));
            },
        ]);
    }
}
/** End of Framework **/