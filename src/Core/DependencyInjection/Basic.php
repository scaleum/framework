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
use Scaleum\Config\Config;
use Scaleum\Config\LoaderResolver;
use Scaleum\Core\ContainerConfiguratorInterface;
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
class Basic implements ContainerConfiguratorInterface {
    public function configure(ContainerBuilder $builder): void {
        $builder
            ->useAttributes(false)
            ->useAutowiring(true);

        $builder->addDefinitions([
            # scalar definitions
            'kernel.version'       => '1.0.0',
            'kernel.sapi_type'     => Explorer::getType(),
            'kernel.sapi_family'   => Explorer::getTypeFamily(),

            # classes definitions
            KernelInterface::class => \DI\get('kernel'),
            EventManager::class    => \DI\autowire(),
            ServiceManager::class  => \DI\autowire(),
            LoggerManager::class   => \DI\autowire(),
            
            LoaderResolver::class  => \DI\autowire()
                ->constructor(\DI\get('environment')),
            Config::class          => \DI\autowire()
                ->constructorParameter('resolver', \DI\get(LoaderResolver::class)),

            // FileResolver::class    => \DI\autowire()
            //     ->method('addPath', \DI\get('kernel.project_dir')),

            # aliases definitions
            'event.manager'        => \DI\get(EventManager::class),
            'log.manager'          => \DI\get(LoggerManager::class),
            // 'file.resolver'        => \DI\get(FileResolver::class),
            // 'config'               => \DI\get(Config::class),
            // 'config.separator'     => '.',

            # services
            'service.manager'      => \DI\get(ServiceManager::class),

        ]);
    }
}
/** End of Framework **/