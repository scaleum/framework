[Повернутись до змісту](../../index.md)

[EN](../../../en/components/http/application.md) | **UK** | [RU](../../../ru/components/http/application.md)
# Application

Клас `Http\Application` — це реалізація ядра `KernelAbstract`, спеціально для роботи в режимі HTTP.

> Важливо: надалі можливі інші реалізації ядра, наприклад для консольних застосунків.

## Призначення

- Прийом вхідного HTTP-запиту ([InboundRequest](./components/http/inbound-request.md))
- Пошук маршруту та контролера
- Виклик контролера для обробки запиту
- Отримання відповіді у вигляді об’єкта `ResponderInterface`
- Відправка відповіді клієнту на рівні ядра (`KernelAbstract`)

## Зв’язок з ядром

`Http\Application` розширює `KernelAbstract` і реалізує специфічну логіку обробки HTTP-запитів.

## Основні завдання

- Створити об’єкт запиту [InboundRequest](./components/http/inbound-request.md)
- Передати його в [RequestHandler](./components/http/request-handler.md)
- Знайти контролер через [ControllerResolver](./components/http/controller-resolver.md)
- Викликати контролер через [ControllerInvoker](./components/http/controller-invoker.md)
- Отримати об’єкт `ResponderInterface`
- Відправити відповідь через `$response->send()` (ядро)

## Важливі класи модуля `Http`

| Клас | Призначення |
|:------|:-----------|
| [InboundRequest](./inbound-request.md) | Представлення вхідного запиту від клієнта |
| [InboundResponse](./inbound-response.md) | Відповідь на запити до зовнішніх систем |
| [OutboundRequest](./outbound-request.md) | Вихідний запит до зовнішніх систем (наприклад, API, CURL) |
| [OutboundResponse](./outbound-response.md) | Відповідь застосунку клієнту |
| [RequestHandler](./request-handler.md) | Обробка запиту, повернення `ResponderInterface` |
| [DependencyInjection\Appendix](#)   | Конфігуратор DI: реєструє сервіси та налаштування модуля |
| [DependencyInjection\Routing](./routing.md)   | Конфігуратор DI: реєструє сервіси та налаштування модуля |
| [ControllerResolver](./controller-resolver.md) | Пошук контролера за маршрутом |
| [ControllerInvoker](./controller-invoker.md) | Виклик контролера з передачею аргументів |
| [Uri](./uri.md) | Робота з URI-записами |
| [Message](./message.md), [Stream](./components/http/stream.md), [StreamTrait](./components/http/stream-trait.md) | Базові структури для HTTP-повідомлень |
| [HeadersManager](./headers-manager.md) | Клас для керування HTTP-заголовками |
| [MethodDispatcherTrait](./method-dispatcher-trait.md) | Маршрутизація вхідних викликів на методи контролера за шаблоном `HTTP_метод_шлях`, де шлях формується з сегментів маршруту |

## Процес обробки запиту
1. Ініціалізація: `Application::bootstrap()`
   - Встановлення конфігуратора `DependencyInjection\Appendix()` застосунку в системний реєстр.
   - Встановлення конфігуратора `DependencyInjection\Routing` маршрутизації в системний реєстр.
   - Запуск стандартного процесу ядра: завантаження конфігів, реєстрація сервісів, події (`KernelEvents::BOOTSTRAP`).
2. Отримання обробника: `getHandler()` — створюється `HandlerInterface(RequestHandler)`.
3. Обробка запиту `getHandler()->handle()`:
   - Завантаження налаштувань маршрутизації.
   - Ініціалізація об’єкта `InboundRequest::fromGlobals()` на основі суперглобальних змінних (`$_GET`, `$_POST`, `$_SERVER`)
   - Пошук маршруту через `ControllerResolver`
   - Виклик контролера через `ControllerInvoker`
4. Отримання об’єкта `ResponderInterface`.
5. Виклик `$response->send()` на рівні ядра для відправки відповіді клієнту

[Повернутись до змісту](../../index.md)