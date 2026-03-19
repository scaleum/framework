[Повернутись до змісту](../../index.md)

[EN](../../../en/components/console/application.md) | **UK** | [RU](../../../ru/components/console/application.md)
# Application

Клас `Console\Application` — реалізація ядра `KernelAbstract` для роботи в консольному режимі (CLI).<br>
> Важливо: це варіант ядра, оптимізований для виконання консольних команд.

## Призначення

- Реєстрація конфігураторів команд у реєстрі (`kernel.configurators`).
- Ініціалізація та завантаження конфігурації команд через DI.
- Забезпечення обробки CLI-запитів через `CommandHandler`.

## Зв’язок з ядром

`Console\Application` розширює `KernelAbstract`, перевизначаючи метод `bootstrap()` для встановлення специфічних для CLI конфігураторів, зберігаючи загальний життєвий цикл ядра.

## Основні завдання

- Виконати загальний процес `KernelAbstract::bootstrap()` (завантаження конфігів, реєстрація сервісів, події).
- Надати обробник CLI через метод `getHandler()`.

## Важливі класи модуля `Console`

| Клас| Призначення |
|:------|:------|
| [Contracts\CommandInterface](./contracts/command-interface.md) | Інтерфейс для CLI-команд |
| [Contracts\ConsoleRequestInterface](./contracts/console-request-interface.md) | Інтерфейс для об’єкта запиту в консольному режимі |
| [Contracts\ConsoleResponseInterface](./contracts/console-response-interface.md) | Інтерфейс для відповіді в консольному режимі |
| [DependencyInjection\Appendix](#) | Конфігуратор DI: реєструє сервіси та налаштування модуля |
| [DependencyInjection\Commands](./commands.md) | Конфігуратор DI: реєструє сервіси та налаштування модуля |
| [CommandHandler](./command-handler.md) | Завантаження описів команд, реєстрація їх у `CommandDispatcher` |
| [CommandDispatcher](./command-dispatcher.md) | Обробка вхідних команд і виклик відповідних `CommandInterface` |
| [CommandAbstract](./command-abstract.md) | Базовий абстрактний клас для реалізації CLI-команд |
| [ConsoleOptions](./console-options.md) | Клас для парсингу та керування опціями й аргументами командного рядка |
| [LockManager](./lock-manager.md) | Клас для керування lock-файлами в консольних додатках |
| [LockHandle](./lock-handle.md) | Допоміжний клас для керування блокуваннями процесів у консольних додатках |
| [Request](./request.md) | Клас для представлення CLI-запиту |
| [Response](./response.md) | Клас-реалізація інтерфейсу `ConsoleResponseInterface` для формування та відправки виводу в консольному режимі |
<!-- | [HandlerInterface](./handler-interface.md) | Контракт для обробника запитів (`handle(): ResponderInterface`) | -->
<!-- | [ResponderInterface](./responder-interface.md) | Контракт для відповідачів, що повертають код виконання та вивід | -->

## Процес обробки CLI-запиту

1. Ініціалізація: `Application::bootstrap()`
   - Встановлення конфігуратора `DependencyInjection\Appendix` додатку в системний реєстр.
   - Встановлення конфігуратора `DependencyInjection\Commands` додатку в системний реєстр.
   - Запуск стандартного процесу ядра: завантаження конфігів, реєстрація сервісів, події (`KernelEvents::BOOTSTRAP`).
2. Отримання обробника: `getHandler()` — створюється `HandlerInterface(CommandHandler)`.
3. Обробка команди `getHandler()->handle()`:
   - Парсинг аргументів із `$argv` через `Request`.
   - Пошук і виклик потрібної команди через `CommandDispatcher`.
4. Отримання об’єкта `ResponderInterface`.
5. Виклик `$response->send()` на рівні ядра для відправки відповіді клієнту.


[Повернутись до змісту](../../index.md)