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

use Scaleum\Config\LoaderResolver;
use Scaleum\Stdlib\Exceptions\ENotFoundError;
use Scaleum\Stdlib\Exceptions\ERuntimeError;
use Scaleum\Stdlib\Helpers\HttpHelper;
use Scaleum\Stdlib\Helpers\StringHelper;

/**
 * Router
 *
 * @author Maxim Kirichenko <kirichenko.maxim@gmail.com>
 */
class Router {
    protected array $files = [];

    /** @var RouteInterface[]  An array to store the defined routes */
    protected array $routes = [];

    protected ?LoaderResolver $resolver = null;

    public function __construct(?LoaderResolver $resolver = null) {
        if ($resolver !== null) {
            $this->setResolver($resolver);
        }
    }

    public function loadFromFile(string $filename) {
        $this->addRoutes($this->getResolver()->fromFile($filename));
    }

    public function loadFromDir(string $dir) {
        $this->addRoutes($this->getResolver()->fromDir($dir, $this->files));
    }

    public function addRoutes(array $routes): void {
        foreach ($routes as $alias => $route) {
            if (! $route instanceof RouteInterface) {
                if (! is_array($route)) {
                    throw new ERuntimeError('Route must be instance of `RouteInterface` or array');
                }

                $route = new Route($route);
            }

            $this->addRoute($alias, $route);
        }
    }
    public function addRoute(string $alias, RouteInterface $route): void {
        $this->routes[$alias] = $route;
    }

    public function getRoutes(): array {
        return $this->routes;
    }

    public function getRoute(string $alias): RouteInterface {
        if (isset($this->routes[$alias])) {
            return $this->routes[$alias];
        }

        throw new ERuntimeError('Route not found');
    }

    /**
     * Generates a URL based on the given route name and parameters.
     *
     * @param string $name The name of the route.
     * @param array $parameters An associative array of parameters to include in the URL.
     * @return string The generated URL.
     */
    public function getUrl(string $name, array $parameters = []): string {
        if ($route = $this->getRoute($name)) {
            return $route->getUrl($parameters);
        }
        return "#{$name}";
    }

    /**
     * Matches the given URI and HTTP method against the defined routes.
     *
     * @param string $uri The URI to match.
     * @param string $method The HTTP method to match (e.g., GET, POST).
     * @return array An array containing the matched route information.
     */
    public function match(string $uri, string $method = HttpHelper::METHOD_GET): array {
        foreach ($this->routes as $route) {
            if (! $route instanceof RouteInterface) {
                throw new ERuntimeError(
                    sprintf(
                        'Route must be an instance of `RouteInterface` given `%s`',
                        is_object($route) ? StringHelper::className($route, true) : gettype($route)
                    )
                );
            }

            if ($method && ! in_array($method, $route->getMethods())) {
                continue;
            }

            if (preg_match($route->getPath(), $uri, $params)) {
                array_shift($params);

                if (is_array($callback = $route->getCallback())) {
                    if (! isset($callback['controller'])) {
                        throw new ERuntimeError('Controller is not defined');
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
                throw new ERuntimeError('Callback is not defined or not an array');
            }
        }
        throw new ENotFoundError(sprintf('Requested URL "%s" is not found on server', $uri));
    }

    /**
     * Get the value of resolver
     */
    public function getResolver() {
        if (! $this->resolver instanceof LoaderResolver) {
            $this->resolver = new LoaderResolver();
        }
        return $this->resolver;
    }

    /**
     * Set the value of resolver
     *
     * @return  self
     */
    public function setResolver(LoaderResolver $resolver) {
        $this->resolver = $resolver;

        return $this;
    }
}
/** End of Router **/