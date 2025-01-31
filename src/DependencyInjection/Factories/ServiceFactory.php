<?php
declare(strict_types=1);
/**
 * This file is part of Scaleum Framework.
 *
 * (C) 2009-2025 Maxim Kirichenko <kirichenko.maxim@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Scaleum\DependencyInjection\Factories;

use Scaleum\DependencyInjection\Container;

/**
 * ServiceFactory
 * 
 * Example of usage:
 * class Logger {}
 * class UserService {
 *     public function __construct(Logger $logger) {}
 * }
 * $container = ContainerFactory::create();
 * $container->addDefinition(Logger::class, new Logger());
 * $userService = ServiceFactory::create(UserService::class, $container);
 * 
 * @author Maxim Kirichenko <kirichenko.maxim@gmail.com>
 */
class ServiceFactory
{
    public static function create(string $serviceClass, Container $container): object
    {
        if (!class_exists($serviceClass)) {
            throw new \InvalidArgumentException("Class `{$serviceClass}` not found");
        }

        $reflection = new \ReflectionClass($serviceClass);
        $constructor = $reflection->getConstructor();

        if (!$constructor) {
            return new $serviceClass();
        }

        $parameters = [];
        foreach ($constructor->getParameters() as $parameter) {
            $type = $parameter->getType();
            if ($type && !$type->isBuiltin()) {
                $parameters[] = $container->get($type->getName());
            } else {
                throw new \Exception("Can't resolve parameter `{$parameter->getName()}` for class `{$serviceClass}`");
            }
        }

        return $reflection->newInstanceArgs($parameters);
    }
}
/** End of ServiceFactory **/