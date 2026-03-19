[Назад](../application.md) | [Повернутися до змісту](../../../index.md)

[EN](../../../../en/components/console/contracts/console-response-interface.md) | **UK** | [RU](../../../../ru/components/console/contracts/console-response-interface.md)
# ConsoleResponseInterface

`ConsoleResponseInterface` — інтерфейс для відповіді в консольному режимі (CLI), що розширює `ResponderInterface` та визначає код повернення і контент виводу.

## Призначення

- Забезпечити стандартний контракт для формування відповіді в CLI-додатках.
- Гарантувати наявність методів для встановлення та отримання вмісту і коду завершення.
- Визначити набір попередньо встановлених кодів статусу.

## Зв’язок з ядром

Інтерфейс використовується `CommandHandler` та `CommandDispatcher` для передачі результату виконання команд і визначення коду виходу додатку.

## Константи статусів

| Константа                   | Значення | Опис                                               |
|:----------------------------|:---------|:---------------------------------------------------|
| `STATUS_SUCCESS`            | `0`      | Успішне виконання команди                          |
| `STATUS_NOT_FOUND`          | `1`      | Команда не знайдена                                |
| `STATUS_INVALID_PARAMS`     | `2`      | Невірні або відсутні параметри команди             |

## Методи інтерфейсу

```php
interface ConsoleResponseInterface extends ResponderInterface
{
    public function setContent(string $content): void;
    public function getContent(): ?string;
    public function getStatusCode(): int;
    public function setStatusCode(int $statusCode): void;
}
```

| Метод                                    | Опис                                                         |
|:-----------------------------------------|:--------------------------------------------------------------|
| `setContent(string $content): void`      | Встановлює текстовий вміст відповіді.                         |
| `getContent(): ?string`                  | Повертає раніше встановлений вміст або `null`.                |
| `getStatusCode(): int`                   | Повертає код завершення команди (один із `STATUSES`).          |
| `setStatusCode(int $statusCode): void`   | Встановлює код завершення (повинен бути в `STATUSES`).        |

## Приклад реалізації

```php
use Scaleum\Console\Contracts\ConsoleResponseInterface;

class Response implements ConsoleResponseInterface {
    protected ?string $content;
    protected int $statusCode = self::STATUS_SUCCESS;

    public function setContent(string $content): void {
        $this->content = $content;
    }

    public function getContent(): ?string {
        return $this->content;
    }

    public function getStatusCode(): int {
        return $this->statusCode;
    }

    public function setStatusCode(int $statusCode): void {
        if (!in_array($statusCode, self::STATUSES, true)) {
            throw new InvalidArgumentException('Invalid status code');
        }
        $this->statusCode = $statusCode;
    }

    public function send(): void {
        // Вивід вмісту та вихід з кодом
        if ($this->content !== null) {
            fwrite(STDOUT, $this->content . PHP_EOL);
        }
        exit($this->statusCode);
    }
}
```

## Приклад використання

```php
$response = new Response();
$response->setContent("Hello, CLI!");
$response->setStatusCode(Response::STATUS_SUCCESS);
$response->send(); // виведе "Hello, CLI!" і завершить скрипт з кодом 0
```

[Назад](../application.md) | [Повернутися до змісту](../../../index.md)