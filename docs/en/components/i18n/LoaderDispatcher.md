[Back to Contents](../../index.md)

**EN** | [UK](../../../uk/components/i18n/LoaderDispatcher.md) | [RU](../../../ru/components/i18n/LoaderDispatcher.md)
#  LoaderDispatcher

`LoaderDispatcher` is a *Service Locator* for translation loaders. It inherits from `ServiceManager` and provides ready registration of three implemented loaders (`Gettext`, `Ini`, `PhpArray`). It allows obtaining the required loader by its alias without directly creating objects.

##  Properties

| Property            | Type  | Access    | Default Value                                                                                               | Purpose                                                                                                                   |
| ------------------- | ----- | --------- | ----------------------------------------------------------------------------------------------------------- | ------------------------------------------------------------------------------------------------------------------------- |
| `$invokableClasses` | array | protected | `['gettext' => Loaders\Gettext::class, 'ini' => Loaders\Ini::class, 'phparray' => Loaders\PhpArray::class]` | A map of "alias - class" used by `ServiceManager` for lazy instantiation of loader instances.                             |

##  Usage Example

```php
<?php

use Scaleum\i18n\LoaderDispatcher;

$dispatcher = new LoaderDispatcher(); // variable in lowerCamelCase

// Get loader by alias
$iniLoader = $dispatcher->get('ini');

// The returned object type is Scaleum\i18n\Loaders\Ini
$messages = $iniLoader->load(__DIR__ . '/messages/ru.ini');

print_r($messages);
```

##  Extending the List of Loaders

```php
<?php

use Scaleum\i18n\LoaderDispatcher;
use Scaleum\i18n\Loaders\YamlTranslationLoader;

$dispatcher = new LoaderDispatcher();

// Register a custom YAML file loader
$dispatcher->setService('yaml', YamlTranslationLoader::class);

$yamlLoader = $dispatcher->getService('yaml');
```

> The `getService` method is declared in the parent `ServiceManager` and allows dynamically adding new aliases.

[Back to Contents](../../index.md)
