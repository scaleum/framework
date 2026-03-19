[Вернуться к оглавлению](./index.md)

[EN](../en/responder-interface.md) | [UK](../uk/responder-interface.md) | **RU**

# ResponderInterface

`ResponderInterface` - контракт отправителя ответа, который завершает цикл обработки и выполняет фактическую отправку результата.

## Пространство имен

```php
namespace Scaleum\Core\Contracts;
```

## Метод интерфейса

```php
interface ResponderInterface
{
    public function send(): void;
}
```

- `send(): void` - отправляет сформированный результат в целевой контекст выполнения (например, HTTP-ответ или вывод в CLI).

## Где используется

- В обработчиках ядра, реализующих `HandlerInterface`.
- В HTTP и Console сценариях как единая точка финализации ответа.

## Примечание

`ResponderInterface` не определяет формат данных ответа и транспортный механизм, а фиксирует минимальный контракт отправки.

[Вернуться к оглавлению](./index.md)
