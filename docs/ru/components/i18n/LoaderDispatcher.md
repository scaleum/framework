[Вернуться к оглавлению](../../index.md)

# LoaderDispatcher

`LoaderDispatcher` — это *Service Locator* для загрузчиков переводов. Наследует `ServiceManager` и предоставляет готовую регистрацию трёх реализованных загрузчиков (`Gettext`, `Ini`, `PhpArray`). Позволяет получить нужный загрузчик по его алиасу без прямого создания объектов.

## Свойства

| Свойство            | Тип   | Доступ    | Значение по умолчанию                                                                                       | Назначение                                                                                                                |
| ------------------- | ----- | --------- | ----------------------------------------------------------------------------------------------------------- | ------------------------------------------------------------------------------------------------------------------------- |
| `$invokableClasses` | array | protected | `['gettext' => Loaders\Gettext::class, 'ini' => Loaders\Ini::class, 'phparray' => Loaders\PhpArray::class]` | Карта «алиас - класс», используемая `ServiceManager` для ленивого создания экземпляров загрузчиков.|

## Пример использования

```php
<?php

use Scaleum\i18n\LoaderDispatcher;

$dispatcher = new LoaderDispatcher(); // переменная lowerCamelCase

// Получаем загрузчик по алиасу
$iniLoader = $dispatcher->get('ini');

// Тип возвращаемого объекта — Scaleum\i18n\Loaders\Ini
$messages = $iniLoader->load(__DIR__ . '/messages/ru.ini');

print_r($messages);
```

## Расширение списка загрузчиков

```php
<?php

use Scaleum\i18n\LoaderDispatcher;
use Scaleum\i18n\Loaders\YamlTranslationLoader;

$dispatcher = new LoaderDispatcher();

// Регистрируем собственный загрузчик YAML‑файлов
$dispatcher->setService('yaml', YamlTranslationLoader::class);

$yamlLoader = $dispatcher->getService('yaml');
```

> Метод `getService` объявлен в родительском `ServiceManager` и позволяет динамически добавлять новые алиасы.

[Вернуться к оглавлению](../../index.md)
