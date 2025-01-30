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
use Scaleum\DependencyInjection\Container;
use Scaleum\DependencyInjection\Contract\ConfiguratorInterface;
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
class Exceptions implements ConfiguratorInterface {
    public function configure(Container $container): void {
        $container
            ->addDefinitions([
                ExceptionRendererInterface::class => function (ContainerInterface $c) {
                    return match ($mode = $c->get('kernel.sapi_family')) {
                        SapiMode::CONSOLE                 => $c->get('error_renderer.console'),
                        SapiMode::HTTP                    => $c->get('error_renderer.http'),
                        default                           => throw new \RuntimeException(sprintf('SAPI mode "%s" is not supported', $mode->getName()))
                    };
                },
                ExceptionHandlerInterface::class  => function (ContainerInterface $c) {
                    $handler = new ExceptionHandler();
                    $handler->setRenderer($c->get(ExceptionRendererInterface::class));
                    return $handler;
                },
                ExceptionOutputConsole::class     => function (ContainerInterface $c) {
                    return new ExceptionOutputConsole($c->get('error_renderer.config'));
                },
                ExceptionOutputHttp::class        => function (ContainerInterface $c) {
                    return new ExceptionOutputHttp($c->get('error_renderer.config'));
                },
                'error_renderer.console'          => ExceptionOutputConsole::class,
                'error_renderer.http'             => ExceptionOutputHttp::class,
                'error_renderer.config'           => [
                    'base_path'           => '@kernel.project_dir',
                    'include_traces'      => false,
                    'allow_fullnamespace' => true,
                ],
            ], true);
    }
}
/** End of Framework **/