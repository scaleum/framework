[Повернутись до змісту](./index.md)

[EN](../en/responder-interface.md) | **UK** | [RU](../ru/responder-interface.md)
#  ResponderInterface

`ResponderInterface` - контракт відправника відповіді, який завершує цикл обробки та виконує фактичну відправку результату.

##  Простір імен

```php
namespace Scaleum\Core\Contracts;
```

##  Метод інтерфейсу

```php
interface ResponderInterface
{
    public function send(): void;
}
```

- `send(): void` - відправляє сформований результат у цільовий контекст виконання (наприклад, HTTP-відповідь або вивід у CLI).

##  Де використовується

- В обробниках ядра, що реалізують `HandlerInterface`.
- У HTTP та Console сценаріях як єдина точка фіналізації відповіді.

##  Примітка

`ResponderInterface` не визначає формат даних відповіді та транспортний механізм, а фіксує мінімальний контракт відправки.

[Повернутись до змісту](./index.md)
