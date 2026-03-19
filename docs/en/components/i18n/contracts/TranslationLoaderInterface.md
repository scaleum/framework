#  TranslationLoaderInterface

**EN** | [UK](../../../../uk/components/i18n/contracts/TranslationLoaderInterface.md) | [RU](../../../../ru/components/i18n/contracts/TranslationLoaderInterface.md)
**Namespace:** `Scaleum\i18n\Contracts`

The interface describes the mechanism for loading translation files as an `ArrayObject`. It is used by i18n components to load text resources (PHP arrays, JSON, etc.) into the application memory. It allows abstraction of the data source and storage format.

##  Methods

| Signature                             | Return Type     | Purpose                                                                                                                             |
| ------------------------------------- | --------------- | ----------------------------------------------------------------------------------------------------------------------------------- |
| `load(string $filename): ArrayObject` | `ArrayObject`   | Loads a translation file and returns its content as an `ArrayObject`. Should throw an exception on read/parse error.               |

##  Implementation Example

```php
<?php

declare(strict_types=1);

namespace App\I18n\Loader;

use Scaleum\i18n\Contracts\TranslationLoaderInterface;
use ArrayObject;
use RuntimeException;

class PhpTranslationLoader implements TranslationLoaderInterface
{
    public function load(string $filename): ArrayObject
    {
        if (! is_file($filename)) {
            throw new RuntimeException("Translation file not found: {$filename}");
        }

        /** @var array<string, string> $messages */
        $messages = require $filename; // assumes the file returns an array of translations

        return new ArrayObject($messages);
    }
}
```

##  Usage Example

```php
<?php

use App\I18n\Loader\PhpTranslationLoader;

$translationLoader = new PhpTranslationLoader(); // variable in lowerCamelCase

$messages = $translationLoader->load(__DIR__ . '/messages/ru.php');

// Get a specific translation
echo $messages['greeting']; // outputs: Привет
```

##  Integration Recommendations

* **Extensibility**: the implementation can support various formats (YAML, JSON, CSV) — just convert the data into an array.
* **Caching**: with a large number of translation strings, it makes sense to wrap the loader with a cache decorator to avoid reading the file repeatedly.
* **Validation**: verify the correctness of the data structure (keys as strings, values as strings). Avoid mixing different types.