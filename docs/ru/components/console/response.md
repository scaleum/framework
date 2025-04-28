[Назад](./application.md) | [Вернуться к оглавлению](../../index.md)
# Response

`Response` — реализация интерфейса `ConsoleResponseInterface` для формирования и отправки вывода в консольном режиме (CLI) во фреймворке Scaleum.

## Назначение

- Хранить содержимое ответа (`content`) и код завершения процесса (`statusCode`).
- Обеспечивать вывод сообщения в соответствующий поток (`STDOUT` или `STDERR`).
- Реализовывать метод `send()` из `ResponderInterface`.

## Свойства

| Свойство              | Тип      | Описание                                                             |
|:----------------------|:---------|:---------------------------------------------------------------------|
| `private ?string $content`    | `string\|null` | Текстовая нагрузка ответа.                                           |
| `private int $statusCode`     | `int`        | Код завершения (по умолчанию `STATUS_SUCCESS`).                      |

## Конструктор

Нет конструктора: свойства инициализируются по умолчанию. Используйте сеттеры для установки значений.

## Методы

### setContent()
```php
public function setContent(string $content): void
```
- Устанавливает текст, который будет выведен при вызове `send()`.

### getContent()
```php
public function getContent(): ?string
```
- Возвращает ранее установленный текст или `null`, если контент не задан.

### setStatusCode()
```php
public function setStatusCode(int $statusCode): void
```
- Устанавливает код завершения процесса.
- Валидирует, что `$statusCode` входит в массив `ConsoleResponseInterface::STATUSES`.
- При ошибочном значении бросает `InvalidArgumentException`.

### getStatusCode()
```php
public function getStatusCode(): int
```
- Возвращает текущий код завершения.

### send
```php
public function send(): void
```
- Если `$content` не пустой, записывает его в поток:
  - В `STDERR`, если код завершения отличается от `STATUS_SUCCESS`.
  - В `STDOUT`, если код равен `STATUS_SUCCESS`.
- Добавляет символ новой строки.

## Пример использования

```php
use Scaleum\Console\Response;

$response = new Response();
$response->setContent("Operation completed successfully");
$response->setStatusCode(Response::STATUS_SUCCESS);
$response->send();
exit($response->getStatusCode());
```

```php
// В случае ошибки:
$response = new Response();
$response->setContent("Error: Invalid parameters");
$response->setStatusCode(Response::STATUS_INVALID_PARAMS);
$response->send();
exit($response->getStatusCode());
```

[Назад](./application.md) | [Вернуться к оглавлению](../../index.md)

