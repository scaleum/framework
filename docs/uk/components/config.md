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
- Використання вкладених ключів з роздільником (`.`)


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
`setResolver(LoaderResolver $resolver): self` | Призначити завантажувач конфігурацій
`getResolver(): LoaderResolver` | Отримати поточний завантажувач
Успадковується від `Registry` | get(), set(), has(), delete(), merge()

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

[Повернутись до змісту](../index.md)