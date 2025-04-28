[Назад](../application.md) | [Вернуться к оглавлению](../../../index.md)
# ConsoleRequestInterface

`ConsoleRequestInterface` — интерфейс для объекта запроса в консольном режиме (CLI) во фреймворке Scaleum. Определяет метод доступа к необработанным аргументам командной строки.

## Назначение

- Представить аргументы, переданные в скрипт.
- Обеспечить возможность получения списка аргументов для последующей маршрутизации команд.

## Метод интерфейса

```php
interface ConsoleRequestInterface
{
    /**
     * Возвращает массив всех аргументов CLI без изменений.
     *
     * @return array Необработанные аргументы.
     */
    public function getRawArguments(): array;
}
```

- `getRawArguments(): array` — возвращает полный массив аргументов, включая имя скрипта и все параметры.

## Пример реализации

```php
use Scaleum\Console\ConsoleRequestInterface;

class ConsoleRequest implements ConsoleRequestInterface {
    protected array $args;

    public function __construct(array $argv) {
        // Сохраняем оригинальный массив аргументов
        $this->args = $argv;
    }

    public function getRawArguments(): array {
        return $this->args;
    }
}
```

## Пример использования в диспетчере

```php
// В CommandDispatcher::dispatch:
$request = new ConsoleRequest($argv);
$raw    = $request->getRawArguments();
$commandName = $raw[1] ?? null; // первый параметр после имени скрипта
```

[Назад](../application.md) | [Вернуться к оглавлению](../../../index.md)

