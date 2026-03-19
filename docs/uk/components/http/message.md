[Повернутись до змісту](../../index.md)

[EN](../../../en/components/http/message.md) | **UK** | [RU](../../../ru/components/http/message.md)
# Message

`Message` — базовий клас PSR-7 для HTTP-повідомлень, що реалізує `MessageInterface`. Служить основою для запитів і відповідей, керуючи заголовками, тілом та версією протоколу.

## Призначення

- Зберігання та модифікація HTTP-заголовків.
- Зберігання та заміна тіла (`StreamInterface`).
- Керування версією протоколу HTTP.

## Конструктор

```php
public function __construct(
    array $headers = [],
    ?StreamInterface $body = null,
    string $protocol = '1.1'
)
```

- `$headers` — асоціативний масив заголовків `['Name' => ['value1', 'value2'], ...]`.
- `$body` — об’єкт `StreamInterface`; за замовчуванням створюється порожній потік.
- `$protocol` — версія HTTP-протоколу (наприклад, `"1.1"`).

## Методи

| Метод                                           | Опис                                                                     |
|:-------------------------------------------------|:-------------------------------------------------------------------------|
| `getProtocolVersion(): string`                   | Повертає рядок версії протоколу.                                         |
| `withProtocolVersion(string $version): static`   | Повертає клон із вказаною версією протоколу.                             |
| `getHeaders(): array`                            | Повертає всі заголовки у вигляді асоціативного масиву.                   |
| `hasHeader(string $name): bool`                  | Перевіряє наявність заголовка (реєстр не чутливий).                      |
| `getHeader(string $name): array`                 | Повертає масив значень заголовка або порожній масив.                      |
| `getHeaderLine(string $name): string`            | Повертає значення заголовка як один рядок, розділений `", "`.             |
| `withHeader(string $name, string|array $value): static`     | Клонує повідомлення, задаючи заголовок `Name: value`.                    |
| `withAddedHeader(string $name, string|array $value): static`| Клонує та додає значення до існуючого заголовка.                         |
| `withoutHeader(string $name): static`            | Клонує повідомлення без вказаного заголовка.                             |
| `getBody(): StreamInterface`                     | Повертає поточний потік тіла.                                            |
| `withBody(StreamInterface $body): static`        | Клонує повідомлення з новим тілом.                                       |

## Приклади

### 1. Створення базового повідомлення
```php
use Scaleum\Http\Message;
use Scaleum\Http\Stream;

// Порожнє повідомлення з тілом за замовчуванням
$message = new Message();
// Версія протоколу
echo $message->getProtocolVersion(); // '1.1'
```

### 2. Додавання та отримання заголовків
```php
$message = (new Message())
    ->withHeader('Content-Type', 'application/json')
    ->withAddedHeader('X-Custom', ['A', 'B']);

// Перевірка
if ($message->hasHeader('Content-Type')) {
    $line = $message->getHeaderLine('Content-Type'); // 'application/json'
    $all  = $message->getHeader('X-Custom');         // ['A', 'B']
}
```

### 3. Заміна тіла повідомлення
```php
$bodyStream = new Stream(fopen('php://temp', 'r+'));
$bodyStream->write(json_encode(['foo' => 'bar']));
$bodyStream->rewind();

$messageWithBody = $message->withBody($bodyStream);
$data = json_decode((string)$messageWithBody->getBody(), true); // ['foo' => 'bar']
```

### 4. Зміна версії протоколу
```php
$newMessage = $message->withProtocolVersion('2.0');
echo $newMessage->getProtocolVersion(); // '2.0'
```

[Повернутись до змісту](../../index.md)