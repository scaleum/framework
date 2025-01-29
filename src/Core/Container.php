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

namespace Scaleum\Core;

use Psr\Container\ContainerInterface;
use ReflectionClass;
use ReflectionException;
use Scaleum\Stdlib\Exception\NotFoundException;

class Container implements ContainerInterface {
    private array $definitions = [];
    private array $instances   = [];

    /**
     * Регистрация зависимости, переменной или алиаса.
     *
     * @param string $id
     * @param mixed $value
     * @param bool $singleton Определяет, будет ли объект singleton
     */
    public function set(string $id, mixed $value, bool $singleton = true): void {
        if (is_object($value) && !is_callable($value)) {
            $this->instances[$id] = $value;
            return;
        }

        $this->definitions[$id] = [
            'value'     => $value,
            'singleton' => $singleton,
        ];
    }

    /**
     * Возвращает объект или переменную из контейнера.
     *
     * @param string $id
     * @return mixed
     * @throws NotFoundException
     */
    public function get(string $id): mixed {
        // Если класс уже был создан как singleton, возвращаем его
        if (isset($this->instances[$id])) {
            return $this->instances[$id];
        }

        // Если класс зарегистрирован через set(), создаем его
        if (isset($this->definitions[$id])) {
            $definition = $this->definitions[$id];

            if (! is_callable($definition['value'])) {
                if (is_string($definition['value']) && class_exists($definition['value'])) {
                    return $this->resolve($definition['value']);
                }
                return $definition['value']; // Переменные возвращаем как есть
            }

            $instance = $definition['value']($this);

            if ($definition['singleton']) {
                $this->instances[$id] = $instance;
            }

            return $instance;
        }

        // Если класс не зарегистрирован, но существует, выполняем резолвинг
        if (class_exists($id)) {
            return $this->resolve($id);
        }

        throw new NotFoundException("Entry '{$id}' not found.");
    }

    /**
     * Проверяет, зарегистрирован ли сервис или переменная.
     *
     * @param string $id
     * @return bool
     */
    public function has(string $id): bool {
        return isset($this->definitions[$id]) || class_exists($id);
    }

    /**
     * Автоматическое создание объекта через рефлексию.
     *
     * @param string $className
     * @param bool $cache Сохранять ли в $this->instances (по умолчанию true)
     * @return object
     * @throws ReflectionException
     */
    private function resolve(string $className, bool $cache = true): object {
        $reflection = new ReflectionClass($className);

        if (! $reflection->isInstantiable()) {
            throw new ReflectionException("Class '{$className}' is not instantiable.");
        }

        $constructor = $reflection->getConstructor();

        // Если конструктора нет или он пустой – создаем объект без аргументов
        if (! $constructor || $constructor->getParameters() === []) {
            $instance = new $className();
        } else {
            $parameters   = $constructor->getParameters();
            $dependencies = [];

            foreach ($parameters as $parameter) {
                $type = $parameter->getType();

                if (! $type || $type->isBuiltin()) {
                    throw new ReflectionException("Cannot resolve parameter '{$parameter->getName()}' for class '{$className}'.");
                }

                $dependencies[] = $this->get($type->getName());
            }

            $instance = $reflection->newInstanceArgs($dependencies);
        }

        // Если кеширование включено, сохраняем в $this->instances
        if ($cache) {
            $this->instances[$className] = $instance;
        }

        return $instance;
    }

}
