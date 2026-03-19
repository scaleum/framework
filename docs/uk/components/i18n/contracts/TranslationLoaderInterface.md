# TranslationLoaderInterface

[EN](../../../../en/components/i18n/contracts/TranslationLoaderInterface.md) | **UK** | [RU](../../../../ru/components/i18n/contracts/TranslationLoaderInterface.md)
**Простір імен:** `Scaleum\i18n\Contracts`

Інтерфейс описує механізм завантаження файлів перекладів у вигляді об'єкта `ArrayObject`. Використовується компонентами i18n для підвантаження текстових ресурсів (PHP‑масиви, JSON тощо) у пам'ять додатку. Дозволяє абстрагувати джерело даних і формат зберігання.

## Методи

| Підпис                                | Повертаємий тип | Призначення                                                                                                                           |
| ------------------------------------- | --------------- | ------------------------------------------------------------------------------------------------------------------------------------ |
| `load(string $filename): ArrayObject` | `ArrayObject`   | Завантажує файл перекладів і повертає його вміст у вигляді `ArrayObject`. Має кидати виключення при помилці читання/парсингу.         |

## Приклад реалізації

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
        $messages = require $filename; // передбачається, що файл повертає масив перекладів

        return new ArrayObject($messages);
    }
}
```

## Приклад використання

```php
<?php

use App\I18n\Loader\PhpTranslationLoader;

$translationLoader = new PhpTranslationLoader(); // змінна lowerCamelCase

$messages = $translationLoader->load(__DIR__ . '/messages/ru.php');

// Отримуємо конкретний переклад
echo $messages['greeting']; // виведе: Привет
```

## Рекомендації щодо інтеграції

* **Розширюваність**: реалізація може підтримувати різні формати (YAML, JSON, CSV) — достатньо перетворити дані в масив.
* **Кешування**: при великій кількості рядків перекладу має сенс обгорнути завантажувач декоратором‑кешем, щоб не читати файл повторно.
* **Валідація**: перевіряйте коректність структури даних (ключі‑рядки, значення‑рядки). Не допускайте змішування різних типів.