[Повернутись до змісту](../../index.md)

[EN](../../../en/components/http/headers-manager.md) | **UK** | [RU](../../../ru/components/http/headers-manager.md)
# HeadersManager

`HeadersManager` — утилітний клас для керування HTTP-заголовками у вигляді асоціативного масиву.

## Призначення

- Зберігання заголовків та їхніх значень як масиву рядків.
- Перевірка існування та отримання значень заголовків.
- Встановлення, додавання та видалення заголовків.
- Вивантаження заголовків у різних форматах: масив рядків, асоціативний плоский масив.

## Конструктор

```php
public function __construct(array $headers = [])
```

- Приймає масив у форматі `['Name' => ['value1', 'value2'], ...]` або `['Name' => 'value1,value2', ...]`.
- Викликає `setHeaders($headers, true)` для ініціалізації.

## Основні методи

| Метод                                                      | Опис                                                                                           |
|:-----------------------------------------------------------|:---------------------------------------------------------------------------------------------------|
| `hasHeader(string $name): bool`                            | Перевіряє, чи встановлений заголовок з ім’ям `$name`.                                                |
| `getHeader(string $name, mixed $default = null): mixed`    | Повертає перший елемент масиву значень заголовка або `$default`, якщо відсутній.              |
| `getHeaderLine(string $name): ?string`                     | Повертає перше значення заголовка або `null`, без масивів.                                      |
| `setHeader(string $name, string $value): void`             | Встановлює заголовок, перезаписуючи існуючий; розділяє рядок за комою на значення.       |
| `addHeader(string $name, string $value): void`             | Додає значення до існуючого заголовка, не перезаписуючи існуючі.                       |
| `removeHeader(string $name): void`                         | Видаляє заголовок повністю.                                                                       |
| `setHeaders(array $headers, bool $reset = false): void`    | Ітерує переданий масив заголовків, встановлюючи або додаючи їх; при `$reset=true` очищує.    |
| `getAll(): array`                                          | Повертає внутрішній масив заголовків.                                                           |
| `getAsStrings(): array`                                    | Повертає масив рядків виду `"Name: value1, value2"` для кожної пари.                            |
| `getAsFlattened(): array`                                  | Повертає асоціативний масив виду `['Name' => 'value1, value2', ...]`.                           |
| `getCount(): int`                                          | Повертає кількість встановлених заголовків.                                                         |
| `clear(): void`                                            | Повністю очищує всі заголовки.                                                                   |

## Приклади використання

### 1. Ініціалізація та встановлення заголовків
```php
$manager = new HeadersManager([
    'Content-Type' => 'application/json',
    'X-Custom'     => ['A', 'B'],
]);

// Перезапишемо Content-Type
$manager->setHeader('Content-Type', 'text/plain');
echo $manager->getHeader('Content-Type'); // 'text/plain'
```

### 2. Додавання значень до існуючого заголовка
```php
$manager->addHeader('X-Custom', 'C,D');
print_r($manager->getAll());
// ['Content-Type' => ['text/plain'], 'X-Custom' => ['A','B','C','D']]
```

### 3. Видалення заголовка та очищення всіх
```php
$manager->removeHeader('X-Custom');
echo $manager->hasHeader('X-Custom') ? 'yes' : 'no'; // 'no'

$manager->clear();
echo $manager->getCount(); // 0
```

### 4. Вивантаження заголовків у різних форматах
```php
$manager->setHeaders([
    'Accept'  => 'text/html,application/xml',
    'Cache-Control' => ['no-cache', 'must-revalidate'],
]);

// Масив рядків
$lines = $manager->getAsStrings();
// ['Accept: text/html, application/xml', 'Cache-Control: no-cache, must-revalidate']

// Плоский асоціативний масив
$flat = $manager->getAsFlattened();
// ['Accept' => 'text/html, application/xml', 'Cache-Control' => 'no-cache, must-revalidate']
```

[Повернутись до змісту](../../index.md)