[Назад](./application.md) | [Повернутися до змісту](../../index.md)

[EN](../../../en/components/console/response.md) | **UK** | [RU](../../../ru/components/console/response.md)
# Response

`Response` — реалізація інтерфейсу `ConsoleResponseInterface` для формування та відправки виводу в консольному режимі (CLI) у фреймворку Scaleum.

## Призначення

- Зберігати вміст відповіді (`content`) та код завершення процесу (`statusCode`).
- Забезпечувати вивід повідомлення у відповідний потік (`STDOUT` або `STDERR`).
- Реалізовувати метод `send()` з `ResponderInterface`.

## Властивості

| Властивість           | Тип       | Опис                                                                |
|:----------------------|:----------|:-------------------------------------------------------------------|
| `private ?string $content`    | `string\|null` | Текстове навантаження відповіді.                                  |
| `private int $statusCode`     | `int`         | Код завершення (за замовчуванням `STATUS_SUCCESS`).               |

## Конструктор

Конструктора немає: властивості ініціалізуються за замовчуванням. Використовуйте сеттери для встановлення значень.

## Методи

### setContent()
```php
public function setContent(string $content): void
```
- Встановлює текст, який буде виведено при виклику `send()`.

### getContent()
```php
public function getContent(): ?string
```
- Повертає раніше встановлений текст або `null`, якщо контент не заданий.

### setStatusCode()
```php
public function setStatusCode(int $statusCode): void
```
- Встановлює код завершення процесу.
- Валідовує, що `$statusCode` входить до масиву `ConsoleResponseInterface::STATUSES`.
- При некоректному значенні викидає `InvalidArgumentException`.

### getStatusCode()
```php
public function getStatusCode(): int
```
- Повертає поточний код завершення.

### send
```php
public function send(): void
```
- Якщо `$content` не порожній, записує його у потік:
  - У `STDERR`, якщо код завершення відрізняється від `STATUS_SUCCESS`.
  - У `STDOUT`, якщо код дорівнює `STATUS_SUCCESS`.
- Додає символ нового рядка.

## Приклад використання

```php
use Scaleum\Console\Response;

$response = new Response();
$response->setContent("Operation completed successfully");
$response->setStatusCode(Response::STATUS_SUCCESS);
$response->send();
exit($response->getStatusCode());
```

```php
// У разі помилки:
$response = new Response();
$response->setContent("Error: Invalid parameters");
$response->setStatusCode(Response::STATUS_INVALID_PARAMS);
$response->send();
exit($response->getStatusCode());
```

[Назад](./application.md) | [Повернутися до змісту](../../index.md)