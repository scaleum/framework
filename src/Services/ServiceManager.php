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

namespace Scaleum\Services;

use ReflectionClass;
use Scaleum\Stdlib\Base\HydratorInterface;
use Scaleum\Stdlib\Exceptions\EComponentError;
use Scaleum\Stdlib\Exceptions\ENotFoundError;
use Scaleum\Stdlib\Exceptions\ERuntimeError;

/**
 * InstanceManager
 * @author Maxim Kirichenko <kirichenko.maxim@gmail.com>
 */
class ServiceManager implements ServiceProviderInterface {
    protected array $configs          = [];
    protected array $instances        = [];
    protected array $invokableClasses = [];
    protected array $normalized       = [];

    public function getService(string $name, mixed $default = null): mixed {
        $normalizeName = $this->normalized[$name] ?? $this->normalize($name, false);

        if (isset($this->instances[$normalizeName])) {
            return $this->instances[$normalizeName];
        }

        // lazy loading
        $instance = null;
        if (isset($this->invokableClasses[$normalizeName])) {
            $instance = $this->createInstance($normalizeName);
        }

        if ($instance === null) {
            // throw new EMatchError(sprintf('No valid instance was found for "%s"', $_name));
            return $default;
        }

        return $this->instances[$normalizeName] = $instance;
    }

    public function getAll(): array {
        return $this->instances;
    }

    public function hasService(string $name): bool {
        $_name = $this->normalized[$name] ?? $this->normalize($name, false);
        if (isset($this->invokableClasses[$_name]) || isset($this->instances[$_name])) {
            return true;
        }

        return false;
    }

    public function setService(string $name, mixed $definition, bool $override = false): mixed {
        if (! empty($name)) {

            if ($override == false && $this->hasService($name)) {
                return false;
            }

            $_name = $this->normalize($name);

            // Class(with[out] namespace)
            if (is_string($definition)) {
                $className = $definition;
            } // Array [class[,config]]
            elseif (is_array($definition)) {
                if (! isset($definition['class']) /*|| !isset($var['config'])*/) {
                    throw new EComponentError(sprintf("Class '%s' cannot be registered", $name));
                }
                $className             = $definition['class'];
                $this->configs[$_name] = $definition['config'] ?? array_diff_key($definition, array_fill_keys(['class', 'config'], 'empty'));
            } // Object
            elseif (is_object($definition)) {
                $this->instances[$_name] = $definition;
                $className               = get_class($definition);
            } else {
                throw new EComponentError(sprintf("Class '%s' cannot be registered", $name));
            }

            $this->invokableClasses[$_name] = $className;
            return true;
        }

        return false;
    }

    public function unlink(string $name): self {
        if ($this->hasService($name)) {
            $invoked = $this->normalize($name, false);
            unset($this->invokableClasses[$invoked], $this->instances[$invoked]);
        }
        return $this;
    }

    protected function createInstance($normalizeName, array $config = []): object {
        $invokable = $this->invokableClasses[$normalizeName];

        if (! class_exists((string) $invokable)) {
            throw new ENotFoundError(sprintf('%s: failed retrieving "%s" via invokable class "%s"; class does not exist', __METHOD__, $normalizeName, $invokable));
        }

        if (isset($this->configs[$normalizeName])) {
            $config = array_merge($config, $this->configs[$normalizeName]);
        }

        $reflection = new ReflectionClass($invokable);
        if (empty($config)) {
            return $reflection->newInstance();
        }

        $constructor = $reflection->getConstructor();
        if ($constructor === null) {
            return $reflection->newInstance();
        }

        if ($reflection->implementsInterface(HydratorInterface::class)) {
            return $reflection->newInstance($config);
        }

        $parameters = $constructor->getParameters();
        $args       = [];
        foreach ($parameters as $parameter) {
            $name = $parameter->getName();
            if (array_key_exists($name, $config)) {
                $args[] = $config[$name];
            } elseif ($parameter->isDefaultValueAvailable()) {
                $args[] = $parameter->getDefaultValue();
            } else {
                throw new ERuntimeError(sprintf('Missing required parameter "%s" for "%s"', $name, $invokable));
            }
        }

        return $reflection->newInstanceArgs($args);
    }

    protected function normalize(string $name, bool $remember = true) {
        if (isset($this->normalized[$name])) {
            return $this->normalized[$name];
        }

        $_name = preg_replace('/[^\p{L}\p{N}_-]/u', '_', mb_strtolower($name));

        if ($remember == true) {
            $this->normalized[$name] = $_name;
        }
        return $_name;
    }

}

/* End of file InstanceManager.php */
