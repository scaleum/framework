<?php

declare (strict_types = 1);
/**
 * This file is part of Scaleum\Stdlib.
 *
 * (C) 2009-2025 Maxim Kirichenko <kirichenko.maxim@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Scaleum\Stdlib\Base;

use ReflectionClass;
use RuntimeException;
use Scaleum\Stdlib\Exceptions\ERuntimeError;
use Scaleum\Stdlib\Helpers\StringHelper;

/**
 * Hydrator
 *
 * @author Maxim Kirichenko <kirichenko.maxim@gmail.com>
 * @datetime 07.05.2024 11:20:55
 */
class Hydrator implements HydratorInterface {
    use InitTrait;
    public function __construct(array $config = []) {
        $this->init($config, $this);
        $this->ready();
    }

    public function ready(): void {}

    public static function createInstance(mixed $input): mixed {
        $invokable = null;
        $config    = [];

        if (is_string($input)) {
            $invokable = $input;
        } elseif (is_array($input) && count($input) > 0) {
            $invokable = $input['class'] ?? null;
            $config    = $input['config'] ?? array_diff_key($input, array_fill_keys(['class', 'config'], 'empty'));
        }

        if (! class_exists((string) $invokable)) {
            throw new RuntimeException(sprintf('%s: failed retrieving class name "%s" via mixed "%s"; class does not exist', __METHOD__, StringHelper::className($invokable, true), gettype($invokable)));
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
}
/** End of Hydrator **/
