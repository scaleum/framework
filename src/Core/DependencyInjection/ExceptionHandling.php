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
use Scaleum\DependencyInjection\Contracts\ConfiguratorInterface;
use Scaleum\Stdlib\Exceptions\ExceptionHandler;
use Scaleum\Stdlib\Exceptions\ExceptionHandlerInterface;
use Scaleum\Stdlib\Exceptions\ExceptionOutputConsole;
use Scaleum\Stdlib\Exceptions\ExceptionOutputHttp;
use Scaleum\Stdlib\Exceptions\ExceptionRendererInterface;
use Scaleum\Stdlib\SAPI\SapiMode;

/**
 * ExceptionHandling
 *
 * @author Maxim Kirichenko <kirichenko.maxim@gmail.com>
 */
class ExceptionHandling implements ConfiguratorInterface {
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
                    'basePath'           => '@kernel.project_dir',
                    'includeTraces'      => false,
                    'allowFullnamespace' => true,
                ],
            ], true);
    }
}
/** End of ExceptionHandling **/