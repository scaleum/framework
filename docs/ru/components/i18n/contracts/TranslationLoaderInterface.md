# TranslationLoaderInterface

**Пространство имён:** `Scaleum\i18n\Contracts`

Интерфейс описывает механизм загрузки файлов переводов в виде объекта `ArrayObject`. Используется компонентами i18n для подгрузки текстовых ресурсов (PHP‑массивы, JSON и др.) в память приложения. Позволяет абстрагировать источник данных и формат хранения.

## Методы

| Подпись                               | Возвращаемый тип | Назначение                                                                                                                           |
| ------------------------------------- | ---------------- | ------------------------------------------------------------------------------------------------------------------------------------ |
| `load(string $filename): ArrayObject` | `ArrayObject`    | Загружает файл переводов и возвращает его содержимое в виде `ArrayObject`. Должен выбрасывать исключение при ошибке чтения/парсинга. |

## Пример реализации

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
        $messages = require $filename; // предполагается, что файл возвращает массив переводов

        return new ArrayObject($messages);
    }
}
```

## Пример использования

```php
<?php

use App\I18n\Loader\PhpTranslationLoader;

$translationLoader = new PhpTranslationLoader(); // переменная lowerCamelCase

$messages = $translationLoader->load(__DIR__ . '/messages/ru.php');

// Получаем конкретный перевод
echo $messages['greeting']; // выведет: Привет
```

## Рекомендации по интеграции

* **Расширяемость**: реализация может поддерживать различные форматы (YAML, JSON, CSV) — достаточно преобразовать данные в массив.
* **Кеширование**: при большом количестве строк перевода имеет смысл обернуть загрузчик декоратором‑кэшем, чтобы не читать файл повторно.
* **Валидация**: проверяйте корректность структуры данных (ключи‑строки, значения‑строки).  Не допускайте смешения разных типов.