[Back to Contents](../../index.md)

**EN** | [UK](../../../../uk/components/storages/pdo/ModelData.md) | [RU](../../../../ru/components/storages/pdo/ModelData.md)
#  ModelData

`ModelData` is a convenient wrapper-container over a model's attribute array. It inherits from `AttributeContainer` and is located in the namespace `Scaleum\Storages\PDO`. The class performs *naturalization* of the input array (bringing keys/values to a canonical form) and can recursively convert nested objects/models to an array.

##  Methods

| Signature                             | Return Type      | Access    | Purpose                                                                                  |
| ------------------------------------- | ---------------- | --------- | ---------------------------------------------------------------------------------------- |
| `__construct(array $attributes = [])` | —                | public    | Accepts initial attributes, passes them through `ArrayHelper::naturalize()`, and forwards to the base `AttributeContainer`. |
| `toArray(): array`                    | `array`          | public    | Returns the attribute array, normalizing each value beforehand via `normalizeValue()`.  |
| `isEmpty(): bool`                     | `bool`           | public    | Determines if the container is empty (checks attribute count via `getAttributeCount()`). |
| `normalizeValue(mixed $value): mixed` | `mixed`          | protected | Recursively normalizes values: unwraps nested `ModelData`, `ModelAbstract`, objects with a `toArray()` method, or arrays. |

##  `normalizeValue()` Algorithm

1. **Nested `ModelData`** → returns `$value->toArray()`.
2. **Nested model (`ModelAbstract`)** → uses `$value->getData()->toArray()`.
3. **Object with `toArray()` method** → calls it.
4. **Array** → applies normalization recursively to all elements.
5. **Other values** → returns "as is".

##  Usage Example

```php
<?php

declare(strict_types=1);

use Scaleum\Storages\PDO\ModelData;

// Input data (may contain sub-arrays and model objects)
$raw = [
    'id'       => 42,
    'name'     => 'Alice',
    'settings' => [
        'notifications' => true,
    ],
];

$modelData = new ModelData($raw); // lowerCamelCase variable

// Conversion to array (recursively converts nested objects if necessary)
print_r($modelData->toArray());

// Check that the container is not empty
if (! $modelData->isEmpty()) {
    // ...
}
```

##  Practical Tips

* **Immutable approach**: do not modify the attribute array directly — use `AttributeContainer` methods (`setAttribute()`, `getAttribute()`) to maintain integrity.
* **Nested structures**: if your model contains nested models/objects, `ModelData` will automatically "flatten" them, making JSON serialization straightforward.
* **Functional storage**: `ModelData` is convenient to store as a separate model property (`getData()`), keeping business logic separate from data.

[Back to Contents](../../index.md)
