[Назад](./application.md) | [Повернутися до змісту](../../index.md)

[EN](../../../en/components/console/command-dispatcher.md) | **UK** | [RU](../../../ru/components/console/command-dispatcher.md)
# CommandDispatcher

`CommandDispatcher` — центральний компонент модуля консольних команд, відповідальний за реєстрацію доступних команд та їх виклик за іменем на основі вхідного CLI запиту.

## Призначення

- Зберігати колекцію зареєстрованих команд (`CommandInterface`).
- Виконувати потрібну команду за першим аргументом запиту.
- Формувати відповідь `ConsoleResponseInterface` у разі відсутності або помилки команди.

## Властивості

| Властивість          | Тип                            | Опис                                              |
|:---------------------|:-------------------------------|:--------------------------------------------------|
| `private array $commands` | `string => CommandInterface` | Словник імен команд та їх екземплярів.           |

## Методи

### registerCommand()
```php
public function registerCommand(string $name, CommandInterface $command): void
```
- Реєструє команду під ключем `$name`.
- Дозволяє додавати команди за замовчуванням та з конфігурації.

### dispatch()
```php
public function dispatch(ConsoleRequestInterface $request): ConsoleResponseInterface
```
1. Витягує необроблені аргументи: `$args = $request->getRawArguments()`.
2. Визначає ім'я команди: `$name = $args[0] ?? null`.
3. Якщо команда існує в `$this->commands`, викликає її `execute($request)` і отримує `ConsoleResponseInterface`.
4. Якщо команда не знайдена або не вказана, створює новий `Response` з повідомленням про помилку та статусом `ConsoleResponseInterface::STATUS_NOT_FOUND`.
5. Повертає об'єкт відповіді.

## Приклад використання

```php
use Scaleum\Console\CommandDispatcher;
use App\Commands\HelloCommand;
use Scaleum\Console\ConsoleRequest;

// Реєстрація команд
$dispatcher = new CommandDispatcher();
$dispatcher->registerCommand('hello', new HelloCommand());

// Створення запиту з argv
$request = new ConsoleRequest();
// Припустимо, php script.php hello John

// Виконання команди
$response = $dispatcher->dispatch($request);

// Обробка відповіді
echo $response->getContent();
exit($response->getStatusCode());
```

## Обробка помилок

- Якщо `$request->getRawArguments()` порожній або команда не зареєстрована, буде повернено `ConsoleResponseInterface` з повідомленням про помилку.

[Назад](./application.md) | [Повернутися до змісту](../../index.md)