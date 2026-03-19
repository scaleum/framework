[Повернутись до змісту](../index.md)

[EN](../../en/components/config.md) | **UK** | [RU](../../ru/components/config.md)
# Config

Компонент `Config` у Scaleum забезпечує зручне завантаження, об'єднання та управління конфігураціями додатку.

## Призначення

- Завантаження конфігурацій з різних форматів файлів
- Об'єднання кількох конфігурацій в одну структуру
- Робота з конфігураціями як з вкладеним реєстром (`Registry`)
- Підтримка розширення конфігурацій через оточення (`env`)
- Гнучка заміна завантажувачів форматів через `LoaderDispatcher`


## Основні компоненти

| Клас | Призначення |
|:------|:-----------|
| `Config` | Робота з конфігурацією на рівні додатку |
| `LoaderResolver` | Завантаження конфігурацій з файлів/директорій |
| `LoaderDispatcher` | Менеджер завантажувачів різних форматів (`php`, `ini`, `json`, `xml`) |

## Основні можливості

- Завантаження однієї конфігурації з файлу (`fromFile`)
- Завантаження та об'єднання кількох файлів (`fromFiles`)
- Автоматична підстановка конфігурацій оточення
- Уніфікований доступ до параметрів через `get()` і `set()`
- Строгий типізований доступ через `getString()`, `getInt()`, `getFloat()`, `getBool()`, `getArray()`
- Використання вкладених ключів з роздільником (`.`)
- Явна інтерполяція плейсхолдерів через `resolvePlaceholders()`


## Приклади використання

#### Завантаження конфігурації з одного файлу

```php
$config = new Config();
$config->fromFile('/config/app.php');
```

#### Завантаження кількох файлів конфігурацій
```php
$config = new Config();
$config->fromFiles([
    '/config/database.php',
    '/config/cache.php',
]);
```

#### Доступ до значень конфігурації
```php
$dbHost = $config->get('database.host');
$config->set('app.debug', true);

$port = $config->getInt('database.port', 5432);
$debug = $config->getBool('app.debug', false);
```

#### Завантаження конфігурацій з директорії
```php
$resolver = new LoaderResolver();
$data = $resolver->fromDir('/config');
```

## Підтримка оточень (env)
Якщо встановлено `env`, наприклад *production*, і файл `/config/database.php` існує, то додатково буде підвантажено `/config/production/database.php` і об'єднано.
```php
$resolver = new LoaderResolver('production');
$config = new Config([], '.', $resolver);

$config->fromFile('/config/database.php');
```

## Інтерполяція env-плейсхолдерів (`resolvePlaceholders`)
`resolvePlaceholders()` — це явний opt-in крок, який обробляє плейсхолдери у рядкових значеннях після завантаження/об'єднання конфігурації.

Підтримуваний синтаксис:
- `${VAR}` — обов'язкова змінна
- `${VAR:-default}` — взяти `default`, якщо змінна відсутня
- `${VAR:?message}` — кинути виключення з `message`, якщо змінна відсутня

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

Приклад у PHP-файлі конфігурації:
```php
return [
    'database' => [
        'host' => '${DB_HOST}',
        'port' => '${DB_PORT:-5432}',
        'user' => '${DB_USER:?DB_USER is required}',
    ],
];
```

Опції `resolvePlaceholders()`:
- `strict` (bool): якщо `true`, `${VAR}` без значення викликає виключення
- `allowEmpty` (bool): якщо `false`, порожні env-значення вважаються відсутніми
- `preserveUnknown` (bool): якщо `true`, нерозв'язані плейсхолдери залишаються як є
- `variables` (array|null): передача мапи змінних плейсхолдерів (рекомендовано)

## Структура завантаження
1. `LoaderResolver`
    - Визначає тип файлу за розширенням.
    - Використовує відповідний завантажувач (PHP, JSON, INI, XML).
    - За наявності оточення намагається докачати environment-специфічний файл.

2. `LoaderDispatcher`
    - Реєстрація завантажувачів (phparray, json, ini, xml).
    - Створює завантажувач за запитом.


## Методи `Config`
Метод | Призначення
|:------|:-----------|
`fromFile(string $filename, ?string $key = null): self` | Завантаження конфігурації з файлу
`fromFiles(array $files, ?string $key = null): self` | Завантаження та об'єднання кількох файлів
`resolvePlaceholders(array $options = []): self` | Інтерполяція плейсхолдерів у рядкових значеннях
`getString(string $key, ?string $default = null): string` | Отримати обов'язкове/типізоване рядкове значення
`getInt(string $key, ?int $default = null): int` | Отримати обов'язкове/типізоване цілочисельне значення
`getFloat(string $key, ?float $default = null): float` | Отримати обов'язкове/типізоване float-значення
`getBool(string $key, ?bool $default = null): bool` | Отримати обов'язкове/типізоване bool-значення
`getArray(string $key, ?array $default = null): array` | Отримати обов'язкове/типізоване значення масиву
`setResolver(LoaderResolver $resolver): self` | Призначити завантажувач конфігурацій
`getResolver(): LoaderResolver` | Отримати поточний завантажувач
Успадковується від `Registry` | get(), set(), has(), unset(), merge()

## Приклад повного використання
```php
use Scaleum\Config\Config;
use Scaleum\Config\LoaderResolver;

$resolver = new LoaderResolver('production');

$config = new Config([], '.', $resolver);

// Завантаження з файлів
$config->fromFiles([
    '/config/app.php',
    '/config/database.php',
]);

// Робота з конфігурацією
if ($config->get('app.debug')) {
    echo "Debug mode is enabled";
}

// Отримання бази даних
$dbHost = $config->get('database.host');
```

## Помилки
Виключення | Умова
|:------|:-----------|
`ERuntimeError` | Спроба завантажити непідтримуваний тип файлу
`ENotFoundError` | Обов'язковий ключ не знайдено (типізований getter без default)
`ETypeException` | Тип значення не відповідає типізованому getter

[Повернутись до змісту](../index.md)