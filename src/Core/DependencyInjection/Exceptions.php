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
use Psr\Container\ContainerInterface;
use Scaleum\Core\ContainerConfiguratorInterface;
use Scaleum\Stdlib\Exception\ExceptionHandler;
use Scaleum\Stdlib\Exception\ExceptionHandlerInterface;
use Scaleum\Stdlib\Exception\ExceptionOutputConsole;
use Scaleum\Stdlib\Exception\ExceptionOutputHttp;
use Scaleum\Stdlib\Exception\ExceptionRendererInterface;
use Scaleum\Stdlib\SAPI\SapiMode;

/**
 * Framework
 *
 * @author Maxim Kirichenko <kirichenko.maxim@gmail.com>
 */
class Exceptions implements ContainerConfiguratorInterface {
    public function configure(ContainerBuilder $builder): void {
        $builder->addDefinitions([
            ExceptionOutputConsole::class     => \DI\autowire()
                ->constructor(\DI\get('error_renderer.config')),
            ExceptionOutputHttp::class        => \DI\autowire()
                ->constructor(\DI\get('error_renderer.config')),
            'error_renderer.console'          => \DI\get(ExceptionOutputConsole::class),
            'error_renderer.http'             => \DI\get(ExceptionOutputHttp::class),
            'error_renderer.config'           => \DI\add([
                'base_path'           => \DI\get('kernel.project_dir'),
                'include_traces'      => false,
                'allow_fullnamespace' => true,
            ]),

            ExceptionRendererInterface::class => function (ContainerInterface $self) {
                return match ($mode = $self->get('kernel.sapi_family')) {
                    SapiMode::CONSOLE                 => $self->get('error_renderer.console'),
                    SapiMode::HTTP                    => $self->get('error_renderer.http'),
                    default                           => throw new \RuntimeException(sprintf('SAPI mode "%s" is not supported', $mode->getName()))
                };
            },
            ExceptionHandlerInterface::class  => function (ContainerInterface $self) {
                $handler = new ExceptionHandler();
                $handler->setRenderer($self->get(ExceptionRendererInterface::class));
                return $handler;
            },
        ]);
    }
}
/** End of Framework **/