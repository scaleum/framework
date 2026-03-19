[Back to Contents](../../index.md)

**EN** | [UK](../../../uk/components/i18n/TranslatorTrait.md) | [RU](../../../ru/components/i18n/TranslatorTrait.md)
#  TranslatorTrait

`TranslatorTrait` provides helper methods for quick access to the `Translator` service from any class. The trait accesses the `ServiceLocator`, so the calling code only needs to register the `translator` service — no additional dependency is required.

##  Methods

| Signature | Return Type       | Purpose |
| --------- | ----------------- | ------- |
| `getTranslatorInstance(): ?Translator` | `Translator\|null` | Attempts to get an instance of `Translator` from the `ServiceLocator`. Returns `null` if the service is not registered. |
| `translate(mixed $message, string $textDomain = 'default', ?string $locale = null): string` | `string` | Delegates message translation to the `Translator` service. If the service is unavailable — returns the original message. |

##  Usage Example

```php
<?php

declare(strict_types=1);

namespace App\Service;

use Scaleum\i18n\TranslatorTrait;

class GreetingService
{
    use TranslatorTrait; // include the trait

    public function greet(string $name): string
    {
        // Translation string 'welcome_user' => 'Привет, %name%'
        $template = $this->translate('welcome_user');

        return str_replace('%name%', $name, $template);
    }
}
```

###  Registering the `translator` service

```php
<?php

use Scaleum\i18n\Translator;
use Scaleum\i18n\LoaderDispatcher;
use Scaleum\Services\ServiceLocator;

$dispatcher  = new LoaderDispatcher();
$translator  = new Translator($dispatcher);

// Register translation resource
$translator->addTranslationFile('ini', __DIR__ . '/messages/ru.ini');

// Add to locator
ServiceLocator::add('translator', $translator);
```

After registration, `GreetingService` can use the `greet()` method without direct injection of `Translator`.

[Back to Contents](../../index.md)
