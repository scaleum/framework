# TranslationLoaderAbstract

Базовый абстрактный загрузчик переводов, реализующий `TranslationLoaderInterface` и предоставляющий вспомогательный метод проверки файла. Упрощает создание конкретных загрузчиков, вынося повторяемую логику (существование и доступность файла) в одно место.

## Методы

| Подпись                                                             | Возвращаемый тип | Назначение                                                                                                                                                   |
| ------------------------------------------------------------------- | ---------------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------ |
| `validateFile(string $filename): bool`                              | `bool`           | Проверяет, что файл существует и доступен для чтения (`is_file` && `is_readable`). Возвращает `true`, если файл пригоден для загрузки. |
| `load(string $filename): ArrayObject` *(абстрактный из интерфейса)* | `ArrayObject`    | Должен быть реализован в наследнике и возвращать массив переводов.                                                                                           |

## Пример наследования

```php
<?php

declare(strict_types=1);

namespace App\I18n\Loader;

use Scaleum\i18n\Loaders\TranslationLoaderAbstract;
use ArrayObject;
use Symfony\Component\Yaml\Yaml; // предположим, что доступна библиотека yaml
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

## Пример использования

```php
<?php

use App\I18n\Loader\YamlTranslationLoader;

$loader = new YamlTranslationLoader(); // переменная lowerCamelCase

$file = __DIR__ . '/messages/ru.yaml';

if ($loader->validateFile($file)) {
    $messages = $loader->load($file);
    echo $messages['greeting']; // вывод: Привет
}
```

## Советы по расширению

* Выносите общие проверки (например, ALLOW\_LIST расширений) в базовый класс через дополнительные методы.
* Сочетайте с кэшем, оборачивая загрузчик декоратором для ускорения многократных обращений.
* При необходимости, валидацию формата можно выполнять внутри `validateFile` или в новом защищённом методе, чтобы не загромождать `load`.
