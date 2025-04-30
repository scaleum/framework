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

namespace Scaleum\Storages\PDO;

use Scaleum\Stdlib\Helpers\ArrayHelper;

/**
 * ModelData
 *
 * @author Maxim Kirichenko <kirichenko.maxim@gmail.com>
 */
class ModelData
{
    protected array $data = [];

    public function __construct(array $initialData = [])
    {
        $data = ArrayHelper::naturalize($initialData);
        foreach ($data as $key => $value) {
            $this->__set($key, $value);
        }
    }

    public function __get(string $name): mixed
    {
        $method = 'get' . ucfirst($name);
        if (method_exists($this, $method)) {
            return $this->$method();
        }

        return $this->data[$name] ?? null;
    }

    public function __set(string $name, mixed $value): void
    {
        $method = 'set' . ucfirst($name);
        if (method_exists($this, $method)) {
            $this->$method($value);
            return;
        }

        $this->data[$name] = $value;
    }

    public function toArray(): array
    {
        $result = [];
        foreach ($this->data as $key => $value) {
            $result[$key] = $this->normalizeValue($value);
        }
        return $result;
    }

    protected function normalizeValue(mixed $value): mixed
    {
        // included ModelData
        if ($value instanceof self) {
            return $value->toArray();
        }

        // included ModelAbstract
        if($value instanceof ModelAbstract) {
            return $value->getData()->toArray();
        }

        // anything with toArray() method
        if (is_object($value) && method_exists($value, 'toArray')) {
            return $value->toArray();
        }

        // array of models or objects with toArray() method
        if (is_array($value)) {
            return array_map(
                fn($item) => $this->normalizeValue($item),
                $value
            );
        }

        // otherwise, return the value as is
        return $value;
    }    

    public function has(string $key): bool
    {
        return array_key_exists($key, $this->data);
    }

    public function isEmpty(): bool
    {
        return empty($this->data);
    }
}
/** End of ModelData **/