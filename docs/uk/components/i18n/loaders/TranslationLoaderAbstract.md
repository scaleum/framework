# TranslationLoaderAbstract

[EN](../../../../en/components/i18n/loaders/TranslationLoaderAbstract.md) | **UK** | [RU](../../../../ru/components/i18n/loaders/TranslationLoaderAbstract.md)
Базовий абстрактний завантажувач перекладів, який реалізує `TranslationLoaderInterface` та надає допоміжний метод перевірки файлу. Спрощує створення конкретних завантажувачів, виносячи повторювану логіку (існування та доступність файлу) в одне місце.

## Методи

| Підпис                                                              | Повертаємий тип  | Призначення                                                                                                                                                  |
| ------------------------------------------------------------------ | ---------------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------ |
| `validateFile(string $filename): bool`                             | `bool`           | Перевіряє, що файл існує і доступний для читання (`is_file` && `is_readable`). Повертає `true`, якщо файл придатний для завантаження.                         |
| `load(string $filename): ArrayObject` *(абстрактний з інтерфейсу)* | `ArrayObject`    | Має бути реалізований у спадкоємці та повертати масив перекладів.                                                                                            |

## Приклад наслідування

```php
<?php

declare(strict_types=1);

namespace App\I18n\Loader;

use Scaleum\i18n\Loaders\TranslationLoaderAbstract;
use ArrayObject;
use Symfony\Component\Yaml\Yaml; // припустимо, що доступна бібліотека yaml
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

## Приклад використання

```php
<?php

use App\I18n\Loader\YamlTranslationLoader;

$loader = new YamlTranslationLoader(); // змінна lowerCamelCase

$file = __DIR__ . '/messages/ru.yaml';

if ($loader->validateFile($file)) {
    $messages = $loader->load($file);
    echo $messages['greeting']; // вивід: Привіт
}
```

## Поради щодо розширення

* Виносьте спільні перевірки (наприклад, ALLOW\_LIST розширень) у базовий клас через додаткові методи.
* Поєднуйте з кешем, обгортаючи завантажувач декоратором для прискорення багаторазових звернень.
* За потреби, валідацію формату можна виконувати всередині `validateFile` або в новому захищеному методі, щоб не захаращувати `load`.