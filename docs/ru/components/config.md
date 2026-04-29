[Вернуться к оглавлению](../index.md)

[EN](../../en/components/config.md) | [UK](../../uk/components/config.md) | **RU**
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
- Строгий типизированный доступ через `getString()`, `getInt()`, `getFloat()`, `getBool()`, `getArray()`
- Использование вложенных ключей с разделителем (`.`)
- Явная интерполяция плейсхолдеров через `resolvePlaceholders()`


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

$port = $config->getInt('database.port', 5432);
$debug = $config->getBool('app.debug', false);
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

## Интерполяция env-плейсхолдеров (`resolvePlaceholders`)
`resolvePlaceholders()` — это явный opt-in шаг, который обрабатывает плейсхолдеры в строковых значениях после загрузки/объединения конфигурации.

Поддерживаемый синтаксис:
- `${VAR}` — обязательная переменная
- `${VAR:-default}` — взять `default`, если переменная отсутствует
- `${VAR:?message}` — выбросить исключение с `message`, если переменная отсутствует

```php
$config = (new Config([], '.'))
    ->fromFiles([
        '/config/app.php',
        '/config/database.php',
    ])
    ->resolvePlaceholders([
        'strict' => true,
        'allowEmpty' => false,
        'preserveUnknown' => false,
    ]);
```

Пример в PHP-файле конфигурации:
```php
return [
    'database' => [
        'host' => '${DB_HOST}',
        'port' => '${DB_PORT:-5432}',
        'user' => '${DB_USER:?DB_USER is required}',
    ],
];
```

Опции `resolvePlaceholders()`:
- `strict` (bool): если `true`, `${VAR}` без значения вызывает исключение
- `allowEmpty` (bool): если `false`, пустые env-значения считаются отсутствующими
- `preserveUnknown` (bool): если `true`, неразрешенные плейсхолдеры остаются как есть
- `variables` (array|null): передача карты переменных плейсхолдеров (рекомендуется)

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
`resolvePlaceholders(array $options = []): self` | Интерполяция плейсхолдеров в строковых значениях
`getString(string $key, ?string $default = null): string` | Получить обязательное/типизированное строковое значение
`getInt(string $key, ?int $default = null): int` | Получить обязательное/типизированное целочисленное значение
`getFloat(string $key, ?float $default = null): float` | Получить обязательное/типизированное float-значение
`getBool(string $key, ?bool $default = null): bool` | Получить обязательное/типизированное bool-значение
`getArray(string $key, ?array $default = null): array` | Получить обязательное/типизированное значение массива
`setResolver(LoaderResolver $resolver): self` | Назначить загрузчик конфигураций
`getResolver(): LoaderResolver` | Получить текущий загрузчик
Наследуется из `Registry` | get(), set(), has(), unset(), merge()

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
`ENotFoundError` | Обязательный ключ не найден (типизированный getter без default)
`ETypeException` | Тип значения не соответствует типизированному getter

[Вернуться к оглавлению](../index.md)