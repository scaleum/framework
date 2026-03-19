[Back to Contents](./index.md)

**EN** | [UK](../uk/handler-interface.md) | [RU](../ru/handler-interface.md)
#  HandlerInterface

`HandlerInterface` - the contract of the ядро handler that initiates the processing cycle and returns a response object.

##  Namespace

```php
namespace Scaleum\Core\Contracts;
```

##  Event Constants

The interface defines the names of events used during processing:

- `EVENT_GET_REQUEST = 'handle::request'` - event of receiving a request.
- `EVENT_GET_RESPONSE = 'handle::response'` - event of receiving/forming a response.

##  Interface Method

```php
interface HandlerInterface
{
    public const EVENT_GET_REQUEST  = 'handle::request';
    public const EVENT_GET_RESPONSE = 'handle::response';

    public function handle(): ResponderInterface;
}
```

- `handle(): ResponderInterface` - performs processing of the current context and returns an object implementing `ResponderInterface`.

##  Usage

- Contracts and implementations of ядро (`Core`) in HTTP/Console scenarios.
- In conjunction with `ResponderInterface`, which is responsible for the final sending of the result.

##  Note

`HandlerInterface` does not describe the details of receiving the request and building the response but fixes the minimal handler contract for ядро unification.

[Back to Contents](./index.md)
