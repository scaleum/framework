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
use Scaleum\Config\LoaderResolver as ConfigLoader;
use Scaleum\Core\ContainerConfiguratorInterface;
use Scaleum\Core\KernelInterface;
use Scaleum\Core\SAPI\Explorer;
use Scaleum\Events\EventManager;
use Scaleum\Services\ServiceManager;
use Scaleum\Stdlib\Base\FileResolver;
use Scaleum\Stdlib\Exception\ExceptionHandler;
use Scaleum\Stdlib\Exception\ExceptionHandlerInterface;
use Scaleum\Stdlib\Exception\ExceptionRendererInterface;
use Scaleum\Stdlib\Exception\RendererConsole;

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
            'kernel.version'                     => '1.0.0',
            'kernel.sapi_type'                   => Explorer::getType(),
            'kernel.sapi_family'                 => Explorer::getTypeFamily(),

            # classes definitions
            KernelInterface::class               => \DI\get('kernel'),
            EventManager::class                  => \DI\autowire(),
            ServiceManager::class                => \DI\autowire(),

            RendererConsole::class               => \DI\autowire()
                ->constructor([
                    'base_path'           => \DI\get('error_renderer.base_path'),
                    'include_traces'      => \DI\get('error_renderer.include_traces'),
                    'allow_fullnamespace' => \DI\get('error_renderer.allow_fullnamespace'),
                ]),
            'error_renderer.base_path'           => \DI\get('kernel.project_dir'),
            'error_renderer.include_traces'      => false,
            'error_renderer.allow_fullnamespace' => true,

            ExceptionRendererInterface::class    => \DI\get(RendererConsole::class),
            ExceptionHandlerInterface::class     => \DI\autowire(ExceptionHandler::class)
                ->method('setRenderer', \DI\get(ExceptionRendererInterface::class)),

            ConfigLoader::class                  => \DI\autowire()
                ->constructorParameter('env', \DI\get('environment')),
            Config::class                        => \DI\autowire()
                ->constructorParameter('separator', \DI\get('config.separator'))
                ->constructorParameter('resolver', \DI\get(ConfigLoader::class)),

            FileResolver::class                  => \DI\autowire()
                ->method('addPath', \DI\get('kernel.project_dir')),

            # aliases definitions
            'event-manager'                      => \DI\get(EventManager::class),
            'service-manager'                    => \DI\get(ServiceManager::class),

            'file-resolver'                      => \DI\get(FileResolver::class),
            'config'                             => \DI\get(Config::class),
            'config.separator'                   => '.',

            # services
            'services'                           => \DI\add([
                'config',
                'file-resolver',
            ]),

        ]);
    }
}
/** End of Framework **/