[Back to Contents](../../../index.md)

**EN** | [UK](../../../../uk/components/i18n/loaders/Ini.md) | [RU](../../../../ru/components/i18n/loaders/Ini.md)
#  Ini

Translation loader class from `.ini` files. Inherits from `TranslationLoaderAbstract` and uses `parse_ini_file` to convert data into an `ArrayObject`.

##  Methods

| Signature                                           | Return Type     | Access    | Purpose                                                                                                 |
| -------------------------------------------------- | --------------- | --------- | ------------------------------------------------------------------------------------------------------- |
| `load(string $filename): ArrayObject`              | `ArrayObject`   | public    | Validates the file via `validateFile`, reads content with `parse_ini_file`, converts to `ArrayObject`. |
| `flatten(array $data, string $prefix = ''): array` | `array`         | protected | Recursively converts a multi-level array into a flat one (`section.key`). Used internally by `load`.    |

##  Usage Example

```php
<?php

use Scaleum\i18n\Loaders\Ini;

$loader = new Ini(); // переменная lowerCamelCase

$messages = $loader->load(__DIR__ . '/messages/ru.ini');

echo $messages['greeting']; // Привет
```

##  Example `ru.ini`

```ini
[greetings]
hello = "Привет"

[farewell]
bye = "Пока"
```

##  Practical Recommendations

* **Sections** in the `.ini` file are automatically combined with keys using a dot (`section.key`).
* **Typing**: `INI_SCANNER_TYPED` preserves numeric and boolean values. Harmless for string messages but consider during validation.
* **Cache**: as with other loaders, it is recommended to wrap with a cache decorator for high-frequency access.

[Back to Contents](../../../index.md)