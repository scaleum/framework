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

namespace Scaleum\Routing;

/**
 * Router
 *
 * @author Maxim Kirichenko <kirichenko.maxim@gmail.com>
 */
class Router {
    /**
     * @var RouteInterface[] An array to store the defined routes.
     */
    protected array $routes = [];

    public function addRoute(RouteInterface $route): void {
        $this->routes[$route->getName()] = $route;
    }

    public function getRoutes(): array {
        return $this->routes;
    }

    public function getRoute(string $name): RouteInterface {
        foreach ($this->routes as $route) {
            if ($route->getName() === $name) {
                return $route;
            }
        }
        throw new \RuntimeException('Route not found');
    }

    /**
     * Generates a URL based on the given route name and parameters.
     *
     * @param string $name The name of the route.
     * @param array $parameters An associative array of parameters to include in the URL.
     * @return string The generated URL.
     */
    public function getUrl(string $name, array $parameters = []): string {
        $route = $this->getRoute($name);
        return $route->getUrl($parameters);
    }

    /**
     * Matches the given URI and HTTP method against the defined routes.
     *
     * @param string $uri The URI to match.
     * @param string $method The HTTP method to match (e.g., GET, POST).
     * @return array An array containing the matched route information.
     */
    public function match(string $uri, string $method = Route::HTTP_GET): array {
        foreach ($this->routes as $route) {
            if (! $route instanceof RouteInterface) {
                throw new \RuntimeException('Route must be instance of RouteInterface');
            }

            if ($method && ! in_array($method, $route->getMethods())) {
                continue;
            }

            if (preg_match($route->getPath(), $uri, $params)) {
                array_shift($params);

                if (is_array($callback = $route->getCallback())) {
                    if (! isset($callback['controller'])) {
                        throw new \RuntimeException('Route callback controller is not defined');
                    } elseif (! is_array($callback['controller'])) {
                        $callback['controller'] = ['class' => $callback['controller'], 'args' => []];
                    }

                    if (isset($callback['method']) && ! empty($callback['method'])) {
                        foreach ($params as $key => $param) {
                            $pattern = "/{:$key}/";
                            if (preg_match($pattern, $callback['method'])) {
                                $callback['method'] = preg_replace($pattern, $param, $callback['method']);
                                unset($params[$key]);
                            }
                        }
                    } else {
                        $callback['method'] = 'index';
                    }

                    // parse args
                    $args = [];
                    foreach (array_values($params) as $param) {
                        $arg  = explode('/', $param);
                        $args = array_merge($args, $arg);
                    }

                    $callback['args'] = (isset($callback['args']) && is_array($callback['args'])) ? array_merge($callback['args'], $args) : $args;

                    return [
                        'uri'      => $uri,
                        'callback' => $callback,
                    ];
                }
                throw new \RuntimeException('Route callback is not defined or not an array');
            }
        }
        throw new \RuntimeException('Route not found');
    }
}
/** End of Router **/