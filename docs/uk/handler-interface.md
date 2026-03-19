[Повернутись до змісту](./index.md)

[EN](../en/handler-interface.md) | **UK** | [RU](../ru/handler-interface.md)
#  HandlerInterface

`HandlerInterface` - контракт обробника ядра, який запускає цикл обробки і повертає об'єкт відповіді.

##  Простір імен

```php
namespace Scaleum\Core\Contracts;
```

##  Константи подій

Інтерфейс задає імена подій, які використовуються в процесі обробки:

- `EVENT_GET_REQUEST = 'handle::request'` - подія отримання запиту.
- `EVENT_GET_RESPONSE = 'handle::response'` - подія отримання/формування відповіді.

##  Метод інтерфейсу

```php
interface HandlerInterface
{
    public const EVENT_GET_REQUEST  = 'handle::request';
    public const EVENT_GET_RESPONSE = 'handle::response';

    public function handle(): ResponderInterface;
}
```

- `handle(): ResponderInterface` - виконує обробку поточного контексту і повертає об'єкт, що реалізує `ResponderInterface`.

##  Де використовується

- Контракти та реалізації ядра (`Core`) в HTTP/Console сценаріях.
- У зв'язці з `ResponderInterface`, який відповідає за фінальну відправку результату.

##  Примітка

`HandlerInterface` не описує деталі отримання запиту і побудови відповіді, а фіксує мінімальний контракт обробника для уніфікації ядра.

[Повернутись до змісту](./index.md)
