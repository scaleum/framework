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

namespace Scaleum\Http\Behaviors;

use Scaleum\Config\LoaderResolver;
use Scaleum\Core\KernelEvents;
use Scaleum\Core\KernelProviderAbstract;
use Scaleum\Events\Event;
use Scaleum\Events\EventHandlerInterface;
use Scaleum\Events\EventManagerInterface;
use Scaleum\Routing\Route;
use Scaleum\Routing\Router;
use Scaleum\Stdlib\Helpers\ArrayHelper;

/**
 * RoutingService
 *
 * @author Maxim Kirichenko <kirichenko.maxim@gmail.com>
 */
class RoutingService extends KernelProviderAbstract implements EventHandlerInterface {
    public function register(EventManagerInterface $events): void {
        $events->on(KernelEvents::BOOTSTRAP, [$this, 'onBootstrap'], -9980);
    }

    public function onBootstrap(Event $event): void {
        /** @var  Router $router */
        $router = $this->getKernel()->getContainer()->get('router');

        /** @var LoaderResolver $loader */
        $loader = $this->getKernel()->getContainer()->get(LoaderResolver::class);
        $routes = [];
        if (file_exists($filename = $this->getKernel()->getContainer()->get('routes.file'))) {
            $routes = $loader->fromFile($filename);
        }

        if (is_dir($directory = $this->getKernel()->getContainer()->get('routes.directory'))) {
            $routes = ArrayHelper::merge($routes, $loader->fromDir($directory));
        }

        foreach ($routes as $alias => $attributes) {
            $router->addRoute($alias, new Route($attributes));
        }
    }
}
/** End of RoutingService **/