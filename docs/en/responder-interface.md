[Back to Contents](./index.md)

**EN** | [UK](../uk/responder-interface.md) | [RU](../ru/responder-interface.md)
#  ResponderInterface

`ResponderInterface` - a contract for the response sender, which completes the processing cycle and performs the actual sending of the result.

##  Namespace

```php
namespace Scaleum\Core\Contracts;
```

##  Interface Method

```php
interface ResponderInterface
{
    public function send(): void;
}
```

- `send(): void` - sends the formed result to the target execution context (e.g., HTTP response or CLI output).

##  Where used

- In ядро handlers implementing `HandlerInterface`.
- In HTTP and Console scenarios as a single point of response finalization.

##  Note

`ResponderInterface` does not define the response data format or transport mechanism, but fixes the minimal sending contract.

[Back to Contents](./index.md)
