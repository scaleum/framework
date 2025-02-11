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

namespace Scaleum\Http;

use Psr\Container\ContainerInterface;
use Scaleum\Events\EventManagerInterface;
use Scaleum\Stdlib\Exceptions\ERuntimeError;

/**
 * ControllerResolver
 *
 * @author Maxim Kirichenko <kirichenko.maxim@gmail.com>
 */
class ControllerResolver {
    public const EVENT_CONTROLLER_RESOLVED = 'controller::resolved';

    protected EventManagerInterface $events;
    public function __construct(protected ContainerInterface $container) {
        if (! ($events = $this->container->get('event.manager')) instanceof EventManagerInterface) {
            throw new \RuntimeException("Event manager is not an instance of EventManagerInterface");
        }
        $this->events = $events;
    }

    public function resolve(array $routeInfo): object {
        if ($callback = $routeInfo['callback']) {
            $controller = $callback['controller'] ?? null;
            $args       = [];

            if (is_array($preset = $callback['controller'])) {
                $controller = $preset['class'] ?? null;
                $args       = $preset['args'] ?? [];
            }

            if ($controller === null) {
                throw new ERuntimeError('Controller is not defined');
            }

            if (! class_exists($controller)) {
                throw new ERuntimeError(sprintf('Controller "%s" does not exist', $controller));
            }

            $result = null;

            if (empty($args)) {
                $result = $this->container->get($controller);
            } else {
                $reflection = new \ReflectionClass($controller);
                if (($constructor = $reflection->getConstructor()) !== null) {
                    $parameters = $constructor->getParameters();
                    $params     = [];
                    foreach ($parameters as $parameter) {
                        $name = $parameter->getName();
                        if (array_key_exists($name, $args)) {
                            $params[] = $args[$name];
                        } elseif ($parameter->isDefaultValueAvailable()) {
                            $params[] = $parameter->getDefaultValue();
                        } else {
                            throw new ERuntimeError(sprintf('Missing required parameter "%s" for "%s"', $name, $controller));
                        }
                    }
                    $result = $reflection->newInstanceArgs($params);

                } else {
                    $result = $reflection->newInstance();
                }
            }

            $this->events->dispatch(self::EVENT_CONTROLLER_RESOLVED, $this, ['controller' => $result]);
            return $result;
        }

        throw new \RuntimeException("Can't resolve controller");
    }
}
/** End of ControllerResolver **/