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

use Scaleum\Stdlib\Base\AttributeContainer;
use Scaleum\Stdlib\Helpers\ArrayHelper;

/**
 * ModelData
 *
 * @author Maxim Kirichenko <kirichenko.maxim@gmail.com>
 */
class ModelData extends AttributeContainer
{
    public function __construct(array $attributes = [])
    {
        parent::__construct(ArrayHelper::naturalize($attributes));
    }

    public function toArray(): array
    {
        $result = [];
        foreach ($this->attributes as $key => $value) {
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

    public function isEmpty(): bool
    {
        return $this->getAttributeCount() === 0;
    }
}
/** End of ModelData **/