[Назад](../application.md) | [Вернуться к оглавлению](../../../index.md)
# ConsoleResponseInterface

`ConsoleResponseInterface` — интерфейс для ответа в консольном режиме (CLI), расширяющий `ResponderInterface` и определяющий код возврата и контент вывода.

## Назначение

- Обеспечить стандартный контракт для формирования ответа в CLI-приложениях.
- Гарантировать наличие методов для установки и получения содержимого и кода завершения.
- Определить набор предустановленных кодов статуса.

## Связь с ядром

Интерфейс используется `CommandHandler` и `CommandDispatcher` для передачи результата выполнения команд и определения кода выхода приложения.

## Константы статусов

| Константа                   | Значение | Описание                                           |
|:----------------------------|:---------|:---------------------------------------------------|
| `STATUS_SUCCESS`            | `0`      | Успешное выполнение команды                        |
| `STATUS_NOT_FOUND`          | `1`      | Команда не найдена                                 |
| `STATUS_INVALID_PARAMS`     | `2`      | Неверные или отсутствующие параметры команды       |

## Методы интерфейса

```php
interface ConsoleResponseInterface extends ResponderInterface
{
    public function setContent(string $content): void;
    public function getContent(): ?string;
    public function getStatusCode(): int;
    public function setStatusCode(int $statusCode): void;
}
```

| Метод                                    | Описание                                                      |
|:-----------------------------------------|:--------------------------------------------------------------|
| `setContent(string $content): void`      | Устанавливает текстовое содержимое ответа.                    |
| `getContent(): ?string`                  | Возвращает ранее установленное содержимое или `null`.         |
| `getStatusCode(): int`                   | Возвращает код завершения команды (одно из `STATUSES`).       |
| `setStatusCode(int $statusCode): void`   | Устанавливает код завершения (должен быть в `STATUSES`).     |

## Пример реализации

```php
use Scaleum\Console\Contracts\ConsoleResponseInterface;

class Response implements ConsoleResponseInterface {
    protected ?string \$content;
    protected int \$statusCode = self::STATUS_SUCCESS;

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
        // Вывод содержимого и выход с кодом
        if ($this->content !== null) {
            fwrite(STDOUT, $this->content . PHP_EOL);
        }
        exit($this->statusCode);
    }
}
```

## Пример использования

```php
$response = new Response();
$response->setContent("Hello, CLI!");
$response->setStatusCode(Response::STATUS_SUCCESS);
$response->send(); // выведет "Hello, CLI!" и завершит скрипт с кодом 0
```

[Назад](../application.md) | [Вернуться к оглавлению](../../../index.md)

