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

namespace Scaleum\DependencyInjection;

use Psr\Container\ContainerInterface;
use ReflectionClass;
use ReflectionException;
use ReflectionFunction;
use Scaleum\DependencyInjection\Helpers\EntryEnvironment;
use Scaleum\DependencyInjection\Helpers\EntryFactory;
use Scaleum\DependencyInjection\Helpers\EntryReference;
use Scaleum\Stdlib\Helpers\ArrayHelper;

class Container implements ContainerInterface {
    private array $definitions = [];
    private array $instances   = [];

    /**
     * Adds a definition to the container.
     *
     * @param string $id The identifier for the definition.
     * @param mixed $value The value associated with the definition.
     * @param bool $singleton Whether the definition should be treated as a singleton. Default is true.
     * @return self Returns the current instance for method chaining.
     */
    public function addDefinition(string $id, mixed $value, bool $singleton = true): self {
        // object instances are always singletons
        if (is_object($value) && ! is_callable($value)) {
            $this->instances[$id] = $value;
            return $this;
        }

        // existing definitions are always overwritten
        if (isset($this->definitions[$id])) {
            // if the definition is already instantiated, throw an exception
            if (isset($this->instances[$id])) {
                throw new ReflectionException("Cannot overwrite a definition that is already instantiated.");
            }

            $existing = $this->definitions[$id];
            if (is_array($existing['value']) && is_array($value)) {
                $value = ArrayHelper::merge($existing['value'], $value);
            }
        }

        // resolve references in arrays
        if (is_array($value)) {
            $resolveReferences = function (array $array) use (&$resolveReferences) {
                foreach ($array as $key => $val) {
                    if (is_array($val)) {
                        $array[$key] = $resolveReferences($val);
                    } elseif (is_string($val) && str_starts_with($val, '@')) {
                        $array[$key] = new EntryReference(substr($val, 1));
                    }
                }
                return $array;
            };

            $value = $resolveReferences($value);
        }

        // resolve references in strings
        if (is_string($value) && str_starts_with($value, '@')) {
            $value = new EntryReference(substr($value, 1));
        }

        $this->definitions[$id] = [
            'value'     => $value,
            'singleton' => $singleton,
        ];

        return $this;
    }

    /**
     * Adds an array of definitions to the container.
     *
     * @param array $definitions An associative array where the key is the identifier and the value is the definition.
     * @param bool $singleton Optional. Whether the definitions should be treated as singletons. Default is true.
     * @return self Returns the instance of the container for method chaining.
     */
    public function addDefinitions(array $definitions, bool $singleton = true): self {
        foreach ($definitions as $id => $definition) {
            $this->addDefinition($id, $definition, $singleton);
        }
        return $this;
    }

    /**
     * Retrieve an entry from the container by its identifier.
     *
     * @param string $id The unique identifier of the entry to retrieve.
     * @return mixed The entry associated with the given identifier.
     */
    public function get(string $id): mixed {
        // if the entry is a singleton, return the instance
        if (isset($this->instances[$id])) {
            return $this->instances[$id];
        }

        // if the entry is a definition, resolve it
        if ($this->has($id)) {
            $definition = $this->definitions[$id];
            
            // if the value is not a callable, return it
            if (! is_callable($definition['value'])) {

                if (is_string($definition['value'])) {
                    if ($this->has($val = $definition['value'])) {
                        return $this->get($val);
                    } elseif (class_exists($val)) {
                        return $this->resolve($val, $definition['singleton']);
                    }
                }

                // supports in arrays
                if (is_array($definition['value'])) {
                    $resolveReferences = function (array $array) use (&$resolveReferences) {
                        foreach ($array as $key => $value) {
                            if (is_array($value)) {
                                $array[$key] = $resolveReferences($value);
                            } elseif ($value instanceof EntryEnvironment) {
                                $array[$key] = $value->resolve();
                            } elseif ($value instanceof EntryReference) {
                                $array[$key] = $this->get($value->getId());
                            } elseif ($value instanceof EntryFactory) {
                                $array[$key] = $value->resolve($this);
                            }
                        }
                        return $array;
                    };
                    return $resolveReferences($definition['value']);
                }

                // supports
                switch (true) {
                case $definition['value'] instanceof EntryEnvironment:
                    return $definition['value']->resolve();
                case $definition['value'] instanceof EntryReference:
                    return $this->get($definition['value']->getId());
                case $definition['value'] instanceof EntryFactory:
                    $instance = $definition['value']->resolve($this);
                    if ($definition['singleton']) {
                        $this->instances[$id] = $instance;
                    }
                    return $instance;
                }

                // default
                return $definition['value'];
            }

            // check if the definition is a factory(with ContainerInterface in parameters)
            $reflection = new ReflectionFunction($definition['value']);
            $params     = $reflection->getParameters();
            if (count($params) > 0 && $params[0]->getType()?->getName() !== ContainerInterface::class) {
                throw new ReflectionException("Factory function '{$id}' must have ContainerInterface as first parameter.");
            }

            $instance = $definition['value']($this);

            if ($definition['singleton']) {
                $this->instances[$id] = $instance;
            }

            return $instance;
        }

        // if the entry is a class, resolve it
        if (class_exists($id)) {
            return $this->resolve($id, false);
        }

        throw new Exceptions\NotFoundException("Entry '{$id}' not found.");
    }

    /**
     * Checks if the container has a service with the given identifier.
     *
     * @param string $id The identifier of the service.
     * @return bool True if the service exists, false otherwise.
     */
    public function has(string $id): bool {
        return isset($this->definitions[$id]);
    }

    /**
     * Resolves an instance of the given class name.
     *
     * @param string $className The fully qualified name of the class to resolve.
     * @param bool $cache Optional. Whether to cache the resolved instance. Default is true.
     * @return object The resolved instance of the class.
     */
    private function resolve(string $className, bool $cache = true): object {
        $reflection = new ReflectionClass($className);

        if (! $reflection->isInstantiable()) {
            throw new ReflectionException("Class '{$className}' is not instantiable.");
        }

        $constructor = $reflection->getConstructor();

        // if there is no constructor or it has no parameters, create an instance without arguments
        if (! $constructor || $constructor->getParameters() === []) {
            $instance = new $className();
        } else {
            $parameters = $constructor->getParameters();
            $args       = [];

            foreach ($parameters as $parameter) {
                $type = $parameter->getType();
                if ($type && $type->isBuiltin()) {
                    if ($parameter->isDefaultValueAvailable()) {
                        $args[] = $parameter->getDefaultValue();
                    } else {
                        throw new ReflectionException("Cannot resolve parameter '{$parameter->getName()}' for class '{$className}'.");
                    }

                } else {
                    $args[] = $this->get($type->getName());
                }
            }

            $instance = $reflection->newInstanceArgs($args);
        }

        // if caching is enabled, save to $this->instances
        if ($cache) {
            $this->instances[$className] = $instance;
        }

        return $instance;
    }

}
