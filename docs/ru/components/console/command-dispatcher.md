[Назад](./application.md) | [Вернуться к оглавлению](../../index.md)
# CommandDispatcher

`CommandDispatcher` — центральный компонент модуля консольных команд, отвечающий за регистрацию доступных команд и их вызов по имени на основании входного запроса CLI.

## Назначение

- Хранить коллекцию зарегистрированных команд (`CommandInterface`).
- Выполнять нужную команду по первой аргументу запроса.
- Формировать ответ `ConsoleResponseInterface` в случае отсутствия или ошибки команды.

## Свойства

| Свойство          | Тип                            | Описание                                          |
|:------------------|:-------------------------------|:--------------------------------------------------|
| `private array $commands` | `string => CommandInterface` | Словарь имен команд и их экземпляров.            |

## Методы

### registerCommand()
```php
public function registerCommand(string $name, CommandInterface $command): void
```
- Регистрирует команду под ключом `$name`.
- Позволяет добавлять команды по умолчанию и из конфигурации.

### dispatch()
```php
public function dispatch(ConsoleRequestInterface $request): ConsoleResponseInterface
```
1. Извлекает необработанные аргументы: `$args = $request->getRawArguments()`.
2. Определяет имя команды: `$name = $args[0] ?? null`.
3. Если команда существует в `$this->commands`, вызывает её `execute($request)` и получает `ConsoleResponseInterface`.
4. Если команда не найдена или не указана, создаёт новый `Response` с сообщением об ошибке и статусом `ConsoleResponseInterface::STATUS_NOT_FOUND`.
5. Возвращает объект ответа.

## Пример использования

```php
use Scaleum\Console\CommandDispatcher;
use App\Commands\HelloCommand;
use Scaleum\Console\ConsoleRequest;

// Регистрация команд
$dispatcher = new CommandDispatcher();
$dispatcher->registerCommand('hello', new HelloCommand());

// Создание запроса из argv
$request = new ConsoleRequest();
// Предположим, php script.php hello John

// Выполнение команды
$response = $dispatcher->dispatch($request);

// Обработка ответа
echo $response->getContent();
exit($response->getStatusCode());
```

## Обработка ошибок

- Если `$request->getRawArguments()` пуст или команда не зарегистрирована, будет возвращён `ConsoleResponseInterface` с сообщением об ошибке.

[Назад](./application.md) | [Вернуться к оглавлению](../../index.md)

