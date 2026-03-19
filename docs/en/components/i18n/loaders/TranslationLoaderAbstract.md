#  TranslationLoaderAbstract

**EN** | [UK](../../../../uk/components/i18n/loaders/TranslationLoaderAbstract.md) | [RU](../../../../ru/components/i18n/loaders/TranslationLoaderAbstract.md)
Base abstract translation loader implementing `TranslationLoaderInterface` and providing a helper method for file validation. Simplifies the creation of specific loaders by extracting repetitive logic (file existence and accessibility) into one place.

##  Methods

| Signature                                                           | Return Type      | Purpose                                                                                                                                                     |
| ------------------------------------------------------------------- | ---------------- | ----------------------------------------------------------------------------------------------------------------------------------------------------------- |
| `validateFile(string $filename): bool`                              | `bool`           | Checks that the file exists and is readable (`is_file` && `is_readable`). Returns `true` if the file is suitable for loading.                              |
| `load(string $filename): ArrayObject` *(abstract from interface)*  | `ArrayObject`    | Must be implemented in the subclass and return an array of translations.                                                                                     |

##  Inheritance Example

```php
<?php

declare(strict_types=1);

namespace App\I18n\Loader;

use Scaleum\i18n\Loaders\TranslationLoaderAbstract;
use ArrayObject;
use Symfony\Component\Yaml\Yaml; // assuming the yaml library is available
use RuntimeException;

class YamlTranslationLoader extends TranslationLoaderAbstract
{
    public function load(string $filename): ArrayObject
    {
        if (! $this->validateFile($filename)) {
            throw new RuntimeException("Translation file invalid: {$filename}");
        }

        /** @var array<string,string> $messages */
        $messages = Yaml::parseFile($filename);

        return new ArrayObject($messages);
    }
}
```

##  Usage Example

```php
<?php

use App\I18n\Loader\YamlTranslationLoader;

$loader = new YamlTranslationLoader(); // variable in lowerCamelCase

$file = __DIR__ . '/messages/ru.yaml';

if ($loader->validateFile($file)) {
    $messages = $loader->load($file);
    echo $messages['greeting']; // output: Привет
}
```

##  Extension Tips

* Extract common checks (e.g., ALLOW_LIST of extensions) into the base class via additional methods.
* Combine with caching by wrapping the loader with a decorator to speed up repeated accesses.
* If necessary, format validation can be performed inside `validateFile` or in a new protected method to avoid cluttering `load`.
