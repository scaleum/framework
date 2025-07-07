[Вернуться к оглавлению](../../index.md)

# TranslatorTrait

`TranslatorTrait` предоставляет вспомогательные методы для быстрого доступа к сервису `Translator` из любого класса. Трейт обращается к `ServiceLocator`, поэтому вызывающему коду достаточно зарегистрировать сервис `translator` — дополнительная зависимость не нужна.

## Методы

| Подпись | Возвращаемый тип   | Назначение |
| --------| ------------------ | ---------- |
| `getTranslatorInstance(): ?Translator`| `Translator\|null` | Пытается получить экземпляр `Translator` из `ServiceLocator`. Возвращает `null`, если сервис не зарегистрирован. |
| `translate(mixed $message, string $textDomain = 'default', ?string $locale = null): string` | `string`           | Делегирует перевод сообщения в сервис `Translator`. Если сервис недоступен — возвращает исходное сообщение.      |

## Пример использования

```php
<?php

declare(strict_types=1);

namespace App\Service;

use Scaleum\i18n\TranslatorTrait;

class GreetingService
{
    use TranslatorTrait; // подключаем трей

    public function greet(string $name): string
    {
        // Строка перевода 'welcome_user' => 'Привет, %name%'
        $template = $this->translate('welcome_user');

        return str_replace('%name%', $name, $template);
    }
}
```

### Регистрация сервиса `translator`

```php
<?php

use Scaleum\i18n\Translator;
use Scaleum\i18n\LoaderDispatcher;
use Scaleum\Services\ServiceLocator;

$dispatcher  = new LoaderDispatcher();
$translator  = new Translator($dispatcher);

// Регистрируем ресурс перевода
$translator->addTranslationFile('ini', __DIR__ . '/messages/ru.ini');

// Добавляем в локатор
ServiceLocator::add('translator', $translator);
```

После регистрации `GreetingService` может использовать метод `greet()` без прямого внедрения `Translator`.

[Вернуться к оглавлению](../../index.md)
