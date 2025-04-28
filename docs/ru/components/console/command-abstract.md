[Назад](./application.md) | [Вернуться к оглавлению](../../index.md)
# CommnadAbstract

`CommnadAbstract` — базовый абстрактный класс для реализации CLI-команд во фреймворке Scaleum. Частично реализует `CommandInterface`, предоставляя удобные методы для работы с опциями, аргументами и выводом сообщений.

## Назначение

- Предоставить доступ к объекту `ConsoleOptions` для управления опциями и аргументами командной строки.
- Обеспечить вывод информации и ошибок в консоль через метод `printLine`.
- Служить общей базой для всех конкретных команд, наследующих общий функционал.

## Связь с интерфейсом

Реализует `Scaleum\Console\Contracts\CommandInterface`, поэтому дочерние классы должны реализовать метод:
```php
public function execute(ConsoleRequestInterface $request): ConsoleResponseInterface;
```

## Свойства

| Свойство                         | Тип                          | Описание                                            |
|:---------------------------------|:-----------------------------|:----------------------------------------------------|
| `protected ?ConsoleOptions $options` | `ConsoleOptions|null`         | Объект для парсинга опций и аргументов CLI-запроса. |

## Методы

### getOptions
```php
public function getOptions(): ConsoleOptions
```
- При первом вызове создаёт и сохраняет новый экземпляр `ConsoleOptions`.
- Возвращает объект, содержащий методы для парсинга и получения значений опций и аргументов.

### printLine
```php
public function printLine(string $message, bool $isError = false): void
```
- Выводит строку `$message` с переводом строки в консоль.
- Если `$isError === true`, выводит в поток `STDERR`, иначе — `STDOUT`.

## Пример реализации команды
```php
use Scaleum\Console\CommnadAbstract;
use Scaleum\Console\Contracts\ConsoleRequestInterface;
use Scaleum\Console\Contracts\ConsoleResponseInterface;

class HelloCommand extends CommnadAbstract
{
    public function execute(ConsoleRequestInterface $request): ConsoleResponseInterface
    {
        // Парсинг опций и аргументов
        $options = $this->getOptions()
            ->setOptsLong(["name::"])
            ->setArgs($request->getRawArguments())
            ->parse();

        $name    = $options->get('name', 'World');

        // Вывод сообщения в STDOUT
        $this->printLine("Hello, {$name}!");

        // Формирование ответа с кодом успеха
        $response = new Response();
        $response->setContent("Greeting sent to {$name}");
        $response->setStatusCode(Response::STATUS_SUCCESS);
        return $response;
    }
}
```

## Рекомендации

- Используйте `getOptions()` для централизованного управления парсингом флагов и параметров CLI.
- Для вывода ошибок применяйте `printLine($msg, true)` чтобы сообщение попало в `STDERR`.
- Наследуйте `CommnadAbstract` во всех классах команд для единообразного поведения.

[Назад](./application.md) | [Вернуться к оглавлению](../../index.md)