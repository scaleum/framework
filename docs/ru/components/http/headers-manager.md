[Вернуться к оглавлению](../../index.md)
# HeadersManager

`HeadersManager` — утилитный класс для управления HTTP-заголовками в виде ассоциативного массива.

## Назначение

- Хранение заголовков и их значений как массива строк.
- Проверка существования и получение значений заголовков.
- Установка, добавление и удаление заголовков.
- Выгрузка заголовков в разных форматах: массив строк, ассоциативный плоский массив.

## Конструктор

```php
public function __construct(array $headers = [])
```

- Принимает массив в формате `['Name' => ['value1', 'value2'], ...]` или `['Name' => 'value1,value2', ...]`.
- Вызывает `setHeaders($headers, true)` для инициализации.

## Основные методы

| Метод                                                      | Описание                                                                                           |
|:-----------------------------------------------------------|:---------------------------------------------------------------------------------------------------|
| `hasHeader(string $name): bool`                            | Проверяет, установлен ли заголовок с именем `$name`.                                                |
| `getHeader(string $name, mixed $default = null): mixed`    | Возвращает первый элемент массива значений заголовка или `$default`, если отсутствует.              |
| `getHeaderLine(string $name): ?string`                     | Возвращает первое значение заголовка или `null`, без массивов.                                      |
| `setHeader(string $name, string $value): void`             | Устанавливает заголовок, перезаписывая существующий; разделяет строку по запятой на значения.       |
| `addHeader(string $name, string $value): void`             | Добавляет значения к существующему заголовку, не перезаписывая существующие.                       |
| `removeHeader(string $name): void`                         | Удаляет заголовок полностью.                                                                       |
| `setHeaders(array $headers, bool $reset = false): void`    | Итерирует переданный массив заголовков, устанавливая или добавляя их; при `$reset=true` очищает.    |
| `getAll(): array`                                          | Возвращает внутренний массив заголовков.                                                           |
| `getAsStrings(): array`                                    | Возвращает массив строк вида `"Name: value1, value2"` для каждой пары.                            |
| `getAsFlattened(): array`                                  | Возвращает ассоциативный массив вида `['Name' => 'value1, value2', ...]`.                           |
| `getCount(): int`                                          | Возвращает число установленных заголовков.                                                         |
| `clear(): void`                                            | Полностью очищает все заголовки.                                                                   |

## Примеры использования

### 1. Инициализация и установка заголовков
```php
$manager = new HeadersManager([
    'Content-Type' => 'application/json',
    'X-Custom'     => ['A', 'B'],
]);

// Перезапишем Content-Type
$manager->setHeader('Content-Type', 'text/plain');
echo $manager->getHeader('Content-Type'); // 'text/plain'
```

### 2. Добавление значений к существующему заголовку
```php
$manager->addHeader('X-Custom', 'C,D');
print_r($manager->getAll());
// ['Content-Type' => ['text/plain'], 'X-Custom' => ['A','B','C','D']]
```

### 3. Удаление заголовка и очистка всех
```php
$manager->removeHeader('X-Custom');
echo $manager->hasHeader('X-Custom') ? 'yes' : 'no'; // 'no'

$manager->clear();
echo $manager->getCount(); // 0
```

### 4. Выгрузка заголовков в разных форматах
```php
$manager->setHeaders([
    'Accept'  => 'text/html,application/xml',
    'Cache-Control' => ['no-cache', 'must-revalidate'],
]);

// Массив строк
$lines = $manager->getAsStrings();
// ['Accept: text/html, application/xml', 'Cache-Control: no-cache, must-revalidate']

// Плоский ассоциативный массив
$flat = $manager->getAsFlattened();
// ['Accept' => 'text/html, application/xml', 'Cache-Control' => 'no-cache, must-revalidate']
```

[Вернуться к оглавлению](../../index.md)