[Повернутись до змісту](../../index.md)

[EN](../../../en/components/i18n/TranslatorTrait.md) | **UK** | [RU](../../../ru/components/i18n/TranslatorTrait.md)
# TranslatorTrait

`TranslatorTrait` надає допоміжні методи для швидкого доступу до сервісу `Translator` з будь-якого класу. Трейт звертається до `ServiceLocator`, тому викликаючому коду достатньо зареєструвати сервіс `translator` — додаткова залежність не потрібна.

## Методи

| Підпис | Тип повернення   | Призначення |
| --------| ------------------ | ---------- |
| `getTranslatorInstance(): ?Translator`| `Translator\|null` | Намагається отримати екземпляр `Translator` з `ServiceLocator`. Повертає `null`, якщо сервіс не зареєстрований. |
| `translate(mixed $message, string $textDomain = 'default', ?string $locale = null): string` | `string`           | Делегує переклад повідомлення у сервіс `Translator`. Якщо сервіс недоступний — повертає вихідне повідомлення.      |

## Приклад використання

```php
<?php

declare(strict_types=1);

namespace App\Service;

use Scaleum\i18n\TranslatorTrait;

class GreetingService
{
    use TranslatorTrait; // підключаємо трейт

    public function greet(string $name): string
    {
        // Рядок перекладу 'welcome_user' => 'Привіт, %name%'
        $template = $this->translate('welcome_user');

        return str_replace('%name%', $name, $template);
    }
}
```

### Реєстрація сервісу `translator`

```php
<?php

use Scaleum\i18n\Translator;
use Scaleum\i18n\LoaderDispatcher;
use Scaleum\Services\ServiceLocator;

$dispatcher  = new LoaderDispatcher();
$translator  = new Translator($dispatcher);

// Реєструємо ресурс перекладу
$translator->addTranslationFile('ini', __DIR__ . '/messages/ru.ini');

// Додаємо в локатор
ServiceLocator::add('translator', $translator);
```

Після реєстрації `GreetingService` може використовувати метод `greet()` без прямого впровадження `Translator`.

[Повернутись до змісту](../../index.md)