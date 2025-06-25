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
use Scaleum\Config\LoaderResolver;
use Scaleum\DependencyInjection\Container;
use Scaleum\DependencyInjection\Contracts\ConfiguratorInterface;
use Scaleum\DependencyInjection\Helpers\Factory;
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
                Router::class      => Factory::create(function (ContainerInterface $c) {
                    $resolver = $c->get(LoaderResolver::class) ?? new LoaderResolver($c->get('environment'));
                    $router   = new Router($resolver);

                    if (file_exists($file = $c->get('routes.file'))) {
                        $router->loadFromFile($file);
                    }

                    if (is_dir($dir = $c->get('routes.directory'))) {
                        $router->loadFromDir($dir);
                    }
                    
                    return $router;
                }),
                'routes.file'      => fn(ContainerInterface $c)      => $c->get('kernel.route_dir') . '/routes.php',
                'routes.directory' => fn(ContainerInterface $c) => $c->get('kernel.route_dir'),
                'router'           => Router::class,
            ]);
    }
}
/** End of Routing **/