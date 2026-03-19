[Back to the table of contents](../../../index.md)

**EN** | [UK](../../../../uk/components/i18n/loaders/PhpArray.md) | [RU](../../../../ru/components/i18n/loaders/PhpArray.md)
#  PhpArray

Translation loader class from PHP arrays (`.php` files returning an array of strings). Inherits `TranslationLoaderAbstract` and implements the `load` method, which handles file inclusion and content validation.

##  Methods

| Signature                             | Return Type     | Access | Purpose                                                                                                                                                                                             |
| ------------------------------------- | --------------- | ------ | --------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| `load(string $filename): ArrayObject` | `ArrayObject`   | public | Validates the file via `validateFile`, includes it using a closure with `require`, ensures the result is an array, otherwise throws `ETypeException`, and returns an `ArrayObject`. |

##  Example translation file `ru.php`

```php
<?php

return [
    'greeting' => 'Привет',
    'farewell' => 'Пока',
];
```

##  Usage example

```php
<?php

use Scaleum\i18n\Loaders\PhpArray;

$loader = new PhpArray(); // variable in lowerCamelCase

$messages = $loader->load(__DIR__ . '/messages/ru.php');

echo $messages['greeting']; // Привет
```

##  Practical recommendations

* The file **must return** an array, not output it — use `return [...];`.
* Keys are case-sensitive; maintain a consistent style (`snake_case` or `dot.notation`).
* For a large number of accesses, wrap the loader with a cache decorator to avoid including the file on every request.
* Exceptions `EInOutException` and `ETypeException` help quickly identify issues with file access or incorrect data format.

[Back to the table of contents](../../../index.md)
