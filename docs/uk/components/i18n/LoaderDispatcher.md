[Вернутися до змісту](../../index.md)

[EN](../../../en/components/i18n/LoaderDispatcher.md) | **UK** | [RU](../../../ru/components/i18n/LoaderDispatcher.md)
# LoaderDispatcher

`LoaderDispatcher` — це *Service Locator* для завантажувачів перекладів. Наслідує `ServiceManager` і надає готову реєстрацію трьох реалізованих завантажувачів (`Gettext`, `Ini`, `PhpArray`). Дозволяє отримати потрібний завантажувач за його аліасом без прямого створення об’єктів.

## Властивості

| Властивість         | Тип   | Доступ    | Значення за замовчуванням                                                                                   | Призначення                                                                                                               |
| ------------------- | ----- | --------- | ----------------------------------------------------------------------------------------------------------- | ------------------------------------------------------------------------------------------------------------------------- |
| `$invokableClasses` | array | protected | `['gettext' => Loaders\Gettext::class, 'ini' => Loaders\Ini::class, 'phparray' => Loaders\PhpArray::class]` | Карта «аліас - клас», що використовується `ServiceManager` для лінивого створення екземплярів завантажувачів.              |

## Приклад використання

```php
<?php

use Scaleum\i18n\LoaderDispatcher;

$dispatcher = new LoaderDispatcher(); // змінна lowerCamelCase

// Отримуємо завантажувач за аліасом
$iniLoader = $dispatcher->get('ini');

// Тип поверненого об’єкта — Scaleum\i18n\Loaders\Ini
$messages = $iniLoader->load(__DIR__ . '/messages/ru.ini');

print_r($messages);
```

## Розширення списку завантажувачів

```php
<?php

use Scaleum\i18n\LoaderDispatcher;
use Scaleum\i18n\Loaders\YamlTranslationLoader;

$dispatcher = new LoaderDispatcher();

// Реєструємо власний завантажувач YAML‑файлів
$dispatcher->setService('yaml', YamlTranslationLoader::class);

$yamlLoader = $dispatcher->getService('yaml');
```

> Метод `getService` оголошений у батьківському `ServiceManager` і дозволяє динамічно додавати нові аліаси.

[Вернутися до змісту](../../index.md)