[Назад](../application.md) | [Повернутися до змісту](../../../index.md)

[EN](../../../../en/components/console/contracts/command-interface.md) | **UK** | [RU](../../../../ru/components/console/contracts/command-interface.md)
# CommandInterface

`CommandInterface` — інтерфейс для CLI-команд у фреймворку Scaleum. Визначає базовий контракт, якому повинні відповідати всі команди.

## Призначення

- Забезпечити єдиний метод для виконання команди за вхідним запитом.
- Гарантувати, що кожна команда повертає коректну відповідь, яка реалізує `ConsoleResponseInterface`.

## Метод інтерфейсу

```php
interface CommandInterface
{
    /**
     * Виконує логіку команди на основі вхідного запиту.
     *
     * @param ConsoleRequestInterface $request Об’єкт запиту з аргументами та опціями
     * @return ConsoleResponseInterface Об’єкт відповіді з кодом виконання та виводом
     */
    public function execute(ConsoleRequestInterface $request): ConsoleResponseInterface;
}
```

- `execute(ConsoleRequestInterface $request): ConsoleResponseInterface` — основний метод для запуску команди. Приймає `ConsoleRequestInterface`, що містить початкові аргументи CLI, і повертає `ConsoleResponseInterface` з результатом виконання.

## Приклад реалізації

```php
use Scaleum\Console\CommandAbstract;
use Scaleum\Console\Contracts\ConsoleRequestInterface;
use Scaleum\Console\Contracts\ConsoleResponseInterface;

class HelloCommand extends CommandAbstract implements CommandInterface {
    public function execute(ConsoleRequestInterface $request): ConsoleResponseInterface {
        $name = $request->getArgument('name') ?? 'World';
        $response = new Response();
        $response->setContent("Hello, {$name}!");
        $response->setStatusCode(Response::STATUS_SUCCESS);
        return $response;
    }
}
```
[Назад](../application.md) | [Повернутися до змісту](../../../index.md)