[Назад](./application.md) | [Повернутись до змісту](../../index.md)

[EN](../../../en/components/console/command-abstract.md) | **UK** | [RU](../../../ru/components/console/command-abstract.md)
# CommandAbstract

`CommandAbstract` — базовий абстрактний клас для реалізації CLI-команд у фреймворку Scaleum. Частково реалізує `CommandInterface`, надаючи зручні методи для роботи з опціями, аргументами та виводом повідомлень.

## Призначення

- Надати доступ до об’єкта `ConsoleOptions` для керування опціями та аргументами командного рядка.
- Забезпечити вивід інформації та помилок у консоль через метод `printLine`.
- Служити загальною базою для всіх конкретних команд, що наслідують спільний функціонал.

## Зв’язок з інтерфейсом

Реалізує `Scaleum\Console\Contracts\CommandInterface`, тому дочірні класи повинні реалізувати метод:
```php
public function execute(ConsoleRequestInterface $request): ConsoleResponseInterface;
```

## Властивості

| Властивість                      | Тип                          | Опис                                               |
|:---------------------------------|:-----------------------------|:---------------------------------------------------|
| `protected ?ConsoleOptions $options` | `ConsoleOptions|null`         | Об’єкт для парсингу опцій та аргументів CLI-запиту. |

## Методи

### getOptions
```php
public function getOptions(): ConsoleOptions
```
- При першому виклику створює і зберігає новий екземпляр `ConsoleOptions`.
- Повертає об’єкт, що містить методи для парсингу та отримання значень опцій і аргументів.

### printLine
```php
public function printLine(string $message, bool $isError = false): void
```
- Виводить рядок `$message` з переходом на новий рядок у консоль.
- Якщо `$isError === true`, виводить у потік `STDERR`, інакше — `STDOUT`.

## Приклад реалізації команди
```php
use Scaleum\Console\CommandAbstract;
use Scaleum\Console\Contracts\ConsoleRequestInterface;
use Scaleum\Console\Contracts\ConsoleResponseInterface;

class HelloCommand extends CommandAbstract
{
    public function execute(ConsoleRequestInterface $request): ConsoleResponseInterface
    {
        // Парсинг опцій та аргументів
        $options = $this->getOptions()
            ->setOptsLong(["name::"])
            ->setArgs($request->getRawArguments())
            ->parse();

        $name    = $options->get('name', 'World');

        // Вивід повідомлення у STDOUT
        $this->printLine("Hello, {$name}!");

        // Формування відповіді з кодом успіху
        $response = new Response();
        $response->setContent("Greeting sent to {$name}");
        $response->setStatusCode(Response::STATUS_SUCCESS);
        return $response;
    }
}
```

## Рекомендації

- Використовуйте `getOptions()` для централізованого керування парсингом прапорців і параметрів CLI.
- Для виводу помилок застосовуйте `printLine($msg, true)`, щоб повідомлення потрапило у `STDERR`.
- Наслідуйте `CommandAbstract` у всіх класах команд для уніфікованої поведінки.

[Назад](./application.md) | [Повернутись до змісту](../../index.md)