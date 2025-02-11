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

namespace Scaleum\Http\DependencyInjection;

use Psr\Container\ContainerInterface;
use Scaleum\DependencyInjection\Container;
use Scaleum\DependencyInjection\Contracts\ConfiguratorInterface;
use Scaleum\DependencyInjection\Helpers\Autowire;
use Scaleum\DependencyInjection\Helpers\Factory;
use Scaleum\Http\Behaviors\RoutingService;
use Scaleum\Routing\Router;

/**
 * Routing
 *
 * @author Maxim Kirichenko <kirichenko.maxim@gmail.com>
 */
class Routing implements ConfiguratorInterface {
    public function configure(Container $container): void {
        $container
            ->addDefinitions([
                Router::class         => Autowire::create(),
                RoutingService::class => function (ContainerInterface $c) {
                    $result = new RoutingService($c->get('kernel'));
                    $result->register($c->get('event.manager'));
                    return $result;
                },
                'routes.file'         => Factory::create(function (ContainerInterface $c) {
                    return $c->get('kernel.config_dir') . '/routes.php';
                }),
                'routes.directory'    => Factory::create(function (ContainerInterface $c) {
                    return $c->get('kernel.config_dir') . '/routes';
                }),
                'router'              => Router::class,
            ]);
    }
}
/** End of Routing **/