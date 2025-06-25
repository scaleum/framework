[Вернуться к оглавлению](../../index.md)
# Application

Класс `Console\Application` — реализация ядра `KernelAbstract` для работы в консольном режиме (CLI).<br>
> Важно: это вариант ядра, оптимизированный под выполнение консольных команд.

## Назначение

- Регистрация конфигураторов команд в реестре (`kernel.configurators`).
- Инициализация и загрузка конфигурации команд через DI.
- Обеспечение обработки CLI-запросов через `CommandHandler`.

## Связь с ядром

`Console\Application` расширяет `KernelAbstract`, переопределяя метод `bootstrap()` для установки специфичных для CLI конфигураторов, сохраняя общий жизненный цикл ядра.

## Основные задачи

- Выполнить общий процесс `KernelAbstract::bootstrap()` (загрузка конфигов, регистрация сервисов, события).
- Предоставить обработчик CLI через метод `getHandler()`.

## Важные классы модуля `Console`

| Класс| Назначение |
|:------|:------|
| [Contracts\CommandInterface](./contracts/command-interface.md) | Интерфейс для CLI-команд |
| [Contracts\ConsoleRequestInterface](./contracts/console-request-interface.md) | Интерфейс для объекта запроса в консольном режиме |
| [Contracts\ConsoleResponseInterface](./contracts/console-response-interface.md) | Интерфейс для ответа в консольном режиме |
| [DependencyInjection\Appendix](#) | Конфигуратор DI: регистрирует сервисы и настройки модуля |
| [DependencyInjection\Commands](./commands.md) | Конфигуратор DI: регистрирует сервисы и настройки модуля |
| [CommandHandler](./command-handler.md) | Загрузка описаний команд, регистрация их в `CommandDispatcher` |
| [CommandDispatcher](./command-dispatcher.md) | Обработка входящих команд и вызов соответствующих `CommandInterface` |
| [CommandAbstract](./command-abstract.md) | Базовый абстрактный класс для реализации CLI-команд |
| [ConsoleOptions](./console-options.md) | Класс для парсинга и управления опциями и аргументами командной строки |
| [LockManager](./lock-manager.md) | Класс для управления lock-файлами в консольных приложениях |
| [LockHandle](./lock-handle.md) | Вспомогательный класс для управления блокировками процессов в консольных приложениях |
| [Request](./request.md) | Класс для представления CLI-запроса |
| [Response](./response.md) | Класс-реализация интерфейса `ConsoleResponseInterface` для формирования и отправки вывода в консольном режиме |
<!-- | [HandlerInterface](./handler-interface.md) | Контракт для обработчика запросов (`handle(): ResponderInterface`) | -->
<!-- | [ResponderInterface](./responder-interface.md) | Контракт для ответчиков, возвращающих код выполнения и вывод | -->

## Процесс обработки CLI-запроса

1. Инициализация: `Application::bootstrap()`
   - Установка конфигуратора `DependencyInjection\Appendix` приложения в системный реестр.
   - Установка конфигуратора `DependencyInjection\Commands` приложения в системный реестр.
   - Запуск стандартного процесса ядра: загрузка конфигов, регистрация сервисов, события (`KernelEvents::BOOTSTRAP`).
2. Получение обработчика: `getHandler()` — создаётся `HandlerInterface(CommandHandler)`.
3. Обработка команды `getHandler()->handle()`:
   - Парсинг аргументов из `$argv` через `Request`.
   - Поиск и вызов нужной команды через `CommandDispatcher`.
4. Получение объекта `ResponderInterface`.
5. Вызов `$response->send()` на уровне ядра для отправки ответа клиенту.


[Вернуться к оглавлению](../../index.md)

