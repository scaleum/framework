[Назад](../application.md) | [Повернутися до змісту](../../../index.md)

[EN](../../../../en/components/console/contracts/console-request-interface.md) | **UK** | [RU](../../../../ru/components/console/contracts/console-request-interface.md)
# ConsoleRequestInterface

`ConsoleRequestInterface` — інтерфейс для об'єкта запиту в консольному режимі (CLI) у фреймворку Scaleum. Визначає метод доступу до необроблених аргументів командного рядка.

## Призначення

- Представити аргументи, передані у скрипт.
- Забезпечити можливість отримання списку аргументів для подальшої маршрутизації команд.

## Метод інтерфейсу

```php
interface ConsoleRequestInterface
{
    /**
     * Повертає масив усіх аргументів CLI без змін.
     *
     * @return array Необроблені аргументи.
     */
    public function getRawArguments(): array;
}
```

- `getRawArguments(): array` — повертає повний масив аргументів, включно з ім'ям скрипта та всіма параметрами.

## Приклад реалізації

```php
use Scaleum\Console\ConsoleRequestInterface;

class ConsoleRequest implements ConsoleRequestInterface {
    protected array $args;

    public function __construct(array $argv) {
        // Зберігаємо оригінальний масив аргументів
        $this->args = $argv;
    }

    public function getRawArguments(): array {
        return $this->args;
    }
}
```

## Приклад використання у диспетчері

```php
// У CommandDispatcher::dispatch:
$request = new ConsoleRequest($argv);
$raw    = $request->getRawArguments();
$commandName = $raw[1] ?? null; // перший параметр після імені скрипта
```

[Назад](../application.md) | [Повернутися до змісту](../../../index.md)