[Вернуться к оглавлению](../index.md)
# Config

Компонент `Config` в Scaleum обеспечивает удобную загрузку, объединение и управление конфигурациями приложения.

## Назначение

- Загрузка конфигураций из разных форматов файлов
- Объединение нескольких конфигураций в одну структуру
- Работа с конфигурациями как с вложенным реестром (`Registry`)
- Поддержка расширения конфигураций через окружение (`env`)
- Гибкая подмена загрузчиков форматов через `LoaderDispatcher`


## Основные компоненты

| Класс | Назначение |
|:------|:-----------|
| `Config` | Работа с конфигурацией на уровне приложения |
| `LoaderResolver` | Загрузка конфигураций из файлов/директорий |
| `LoaderDispatcher` | Менеджер загрузчиков разных форматов (`php`, `ini`, `json`, `xml`) |

## Основные возможности

- Загрузка одной конфигурации из файла (`fromFile`)
- Загрузка и объединение нескольких файлов (`fromFiles`)
- Автоматическая подстановка конфигураций окружения
- Унифицированный доступ к параметрам через `get()` и `set()`
- Использование вложенных ключей с разделителем (`.`)


## Примеры использования

#### Загрузка конфигурации из одного файла

```php
$config = new Config();
$config->fromFile('/config/app.php');
```

#### Загрузка нескольких файлов конфигураций
```php
$config = new Config();
$config->fromFiles([
    '/config/database.php',
    '/config/cache.php',
]);
```

#### Доступ к значениям конфигурации
```php
$dbHost = $config->get('database.host');
$config->set('app.debug', true);
```

#### Загрузка конфигураций из директории
```php
$resolver = new LoaderResolver();
$data = $resolver->fromDir('/config');
```

## Поддержка окружений (env)
Если установлен `env`, например *production*, и файл `/config/database.php` существует, то дополнительно будет подгружен `/config/production/database.php` и объединён.
```php
$resolver = new LoaderResolver('production');
$config = new Config([], '.', $resolver);

$config->fromFile('/config/database.php');
```

## Структура загрузки
1. `LoaderResolver`
    - Определяет тип файла по расширению.
    - Использует соответствующий загрузчик (PHP, JSON, INI, XML).
    - При наличии окружения пытается догрузить environment-специфичный файл.

2. `LoaderDispatcher`
    - Регистрация загрузчиков (phparray, json, ini, xml).
    - Создаёт загрузчик по запросу.


## Методы `Config`
Метод | Назначение
|:------|:-----------|
`fromFile(string $filename, ?string $key = null): self` | Загрузка конфигурации из файла
`fromFiles(array $files, ?string $key = null): self` | Загрузка и объединение нескольких файлов
`setResolver(LoaderResolver $resolver): self` | Назначить загрузчик конфигураций
`getResolver(): LoaderResolver` | Получить текущий загрузчик
Наследуется из `Registry` | get(), set(), has(), delete(), merge()

## Пример полного использования
```php
use Scaleum\Config\Config;
use Scaleum\Config\LoaderResolver;

$resolver = new LoaderResolver('production');

$config = new Config([], '.', $resolver);

// Загрузка из файлов
$config->fromFiles([
    '/config/app.php',
    '/config/database.php',
]);

// Работа с конфигурацией
if ($config->get('app.debug')) {
    echo "Debug mode is enabled";
}

// Получение базы данных
$dbHost = $config->get('database.host');
```

## Ошибки
Исключение | Условие
|:------|:-----------|
`ERuntimeError` | Попытка загрузить неподдерживаемый тип файла

[Вернуться к оглавлению](../index.md)