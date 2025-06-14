[Назад](../application.md) | [Вернуться к оглавлению](../../../index.md)
# CommandInterface

`CommandInterface` — интерфейс для CLI-команд во фреймворке Scaleum. Определяет базовый контракт, которому должны соответствовать все команды.

## Назначение

- Обеспечить единый метод для выполнения команды по входящему запросу.
- Гарантировать, что каждая команда возвращает корректный ответ, реализующий `ConsoleResponseInterface`.

## Метод интерфейса

```php
interface CommandInterface
{
    /**
     * Выполняет логику команды на основании входного запроса.
     *
     * @param ConsoleRequestInterface $request Объект запроса с аргументами и опциями
     * @return ConsoleResponseInterface Объект ответа с кодом выполнения и выводом
     */
    public function execute(ConsoleRequestInterface $request): ConsoleResponseInterface;
}
```

- `execute(ConsoleRequestInterface $request): ConsoleResponseInterface` — основной метод для запуска команды. Принимает `ConsoleRequestInterface`, содержащий исходные аргументы CLI, и возвращает `ConsoleResponseInterface` с результатом выполнения.

## Пример реализации

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
[Назад](../application.md) | [Вернуться к оглавлению](../../../index.md)

