[Вернуться к оглавлению](./index.md)
# Application

Класс `Http\Application` — это реализация ядра `KernelAbstract` специально для работы в режиме HTTP.

> Важно: в дальнейшем возможны другие реализации ядра, например для консольных приложений.

## Назначение

- Приём входящего HTTP-запроса ([InboundRequest](./components/http/inbound-request.md)`)
- Поиск маршрута и контроллера
- Вызов контроллера для обработки запроса
- Получение ответа в виде объекта `ResponderInterface`
- Отправка ответа клиенту на уровне ядра (`KernelAbstract`)

## Связь с ядром

`Http\Application` расширяет `KernelAbstract` и реализует специфическую логику обработки HTTP-запросов.


## Основные задачи

- Создать объект запроса [InboundRequest](./components/http/inbound-request.md)
- Передать его в [RequestHandler](./components/http/request-handler.md)
- Найти контроллер через [ControllerResolver](./components/http/controller-resolver.md)
- Вызвать контроллер через [ControllerInvoker](./components/http/controller-invoker.md)
- Получить объект `ResponderInterface`
- Отправить ответ через `$response->send()` (ядро)

## Важные классы модуля `Http`

| Класс | Назначение |
|:------|:-----------|
| [InboundRequest](./components/http/inbound-request.md) | Представление входящего запроса от клиента |
| [InboundRespons](./components/http/inbound-response.md) | Ответ на запросы к внешним системам |
| [OutboundRequest](./components/http/outbound-request.md) | Исходящий запрос к внешним системам (например, API, CURL) |
| [OutboundResponse](./components/http/outbound-response.md) | Ответ приложения клиенту |
| [RequestHandler](./components/http/request-handler.md) | Обработка запроса, возврат `ResponderInterface` |
| [ControllerResolver](./components/http/controller-resolver.md) | Поиск контроллера по маршруту |
| [ControllerInvoker](./components/http/controller-invoker.md) | Вызов контроллера с передачей аргументов |
| [Uri](./components/http/uri.md) | Работа с URI-записями |
| [Message](./components/http/message.md), [Stream](./components/http/stream.md), [StreamTrait](./components/http/stream-trait.md) | Базовые структуры для HTTP-сообщений |
[HeadersManager](./components/http/headers-manager.md) | Класс для управления HTTP-заголовками
[MethodDispatcherTrait](./components/http/method-dispatcher-trait.md) | Маршрутизация входящих вызовов на методы контроллера по шаблону `HTTP_метод_путь`, где путь формируется из сегментов маршрута

## Процесс обработки запроса

1. Инициализация объекта `InboundRequest` на основе суперглобальных переменных (`$_GET`, `$_POST`, `$_SERVER`).
2. Передача запроса в `RequestHandler`.
3. Поиск маршрута через `ControllerResolver`.
4. Вызов контроллера через `ControllerInvoker`.
5. Получение объекта `ResponderInterface`.
6. Вызов `$response->send()` на уровне ядра для отправки ответа клиенту.

[Вернуться к оглавлению](./index.md)