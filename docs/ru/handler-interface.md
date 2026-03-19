[Вернуться к оглавлению](./index.md)

[EN](../en/handler-interface.md) | [UK](../uk/handler-interface.md) | **RU**
# HandlerInterface

`HandlerInterface` - контракт обработчика ядра, который запускает цикл обработки и возвращает объект ответа.

## Пространство имен

```php
namespace Scaleum\Core\Contracts;
```

## Константы событий

Интерфейс задает имена событий, которые используются в процессе обработки:

- `EVENT_GET_REQUEST = 'handle::request'` - событие получения запроса.
- `EVENT_GET_RESPONSE = 'handle::response'` - событие получения/формирования ответа.

## Метод интерфейса

```php
interface HandlerInterface
{
    public const EVENT_GET_REQUEST  = 'handle::request';
    public const EVENT_GET_RESPONSE = 'handle::response';

    public function handle(): ResponderInterface;
}
```

- `handle(): ResponderInterface` - выполняет обработку текущего контекста и возвращает объект, реализующий `ResponderInterface`.

## Где используется

- Контракты и реализации ядра (`Core`) в HTTP/Console сценариях.
- В связке с `ResponderInterface`, который отвечает за финальную отправку результата.

## Примечание

`HandlerInterface` не описывает детали получения запроса и построения ответа, а фиксирует минимальный контракт обработчика для унификации ядра.

[Вернуться к оглавлению](./index.md)
