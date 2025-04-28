[Вернуться к оглавлению](../../index.md)
# Message

`Message` — базовый класс PSR-7 для HTTP-сообщений, реализующий `MessageInterface`. Служит основой для запросов и ответов, управляя заголовками, телом и версией протокола.

## Назначение

- Хранение и модификация HTTP-заголовков.
- Хранение и замена тела (`StreamInterface`).
- Управление версией протокола HTTP.

## Конструктор

```php
public function __construct(
    array $headers = [],
    ?StreamInterface $body = null,
    string $protocol = '1.1'
)
```

- `$headers` — ассоциативный массив заголовков `['Name' => ['value1', 'value2'], ...]`.
- `$body` — объект `StreamInterface`; по умолчанию создаётся пустой поток.
- `$protocol` — версия HTTP-протокола (например, `"1.1"`).

## Методы

| Метод                                           | Описание                                                                  |
|:-------------------------------------------------|:--------------------------------------------------------------------------|
| `getProtocolVersion(): string`                   | Возвращает строку версии протокола.                                       |
| `withProtocolVersion(string $version): static`   | Возвращает клон с указанной версией протокола.                            |
| `getHeaders(): array`                            | Возвращает все заголовки в виде ассоциативного массива.                   |
| `hasHeader(string $name): bool`                  | Проверяет наличие заголовка (регистр не чувствителен).                     |
| `getHeader(string $name): array`                 | Возвращает массив значений заголовка или пустой массив.                   |
| `getHeaderLine(string $name): string`            | Возвращает значения заголовка как одну строку, разделённую `", "`.      |
| `withHeader(string $name, string|array $value): static`     | Клонирует сообщение, задавая заголовок `Name: value`.            |
| `withAddedHeader(string $name, string|array $value): static`| Клонирует и добавляет значение к существующему заголовку.       |
| `withoutHeader(string $name): static`            | Клонирует сообщение без указанного заголовка.                             |
| `getBody(): StreamInterface`                     | Возвращает текущий поток тела.                                            |
| `withBody(StreamInterface $body): static`        | Клонирует сообщение с новым телом.                                        |

## Примеры

### 1. Создание базового сообщения
```php
use Scaleum\Http\Message;
use Scaleum\Http\Stream;

// Пустое сообщение с телом по умолчанию
$message = new Message();
// Версия протокола
echo $message->getProtocolVersion(); // '1.1'
```

### 2. Добавление и получение заголовков
```php
$message = (new Message())
    ->withHeader('Content-Type', 'application/json')
    ->withAddedHeader('X-Custom', ['A', 'B']);

// Проверка
if ($message->hasHeader('Content-Type')) {
    $line = $message->getHeaderLine('Content-Type'); // 'application/json'
    $all  = $message->getHeader('X-Custom');         // ['A', 'B']
}
```

### 3. Замена тела сообщения
```php
$bodyStream = new Stream(fopen('php://temp', 'r+'));
$bodyStream->write(json_encode(['foo' => 'bar']));
$bodyStream->rewind();

$messageWithBody = $message->withBody($bodyStream);
$data = json_decode((string)$messageWithBody->getBody(), true); // ['foo' => 'bar']
```

### 4. Изменение версии протокола
```php
$newMessage = $message->withProtocolVersion('2.0');
echo $newMessage->getProtocolVersion(); // '2.0'
```

[Вернуться к оглавлению](../../index.md)