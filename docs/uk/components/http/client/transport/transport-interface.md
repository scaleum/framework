[Повернутись до змісту](../../../../index.md)

[EN](../../../../../en/components/http/client/transport/transport-interface.md) | **UK** | [RU](../../../../../ru/components/http/client/transport/transport-interface.md)
# TransportInterface

`TransportInterface` — інтерфейс для транспортних механізмів HTTP-клієнта у фреймворку Scaleum. Визначає контракт для відправки `OutboundRequest` та перевірки підтримки транспорту.

## Призначення

- Абстрагувати деталі низькорівневої реалізації відправки HTTP-запитів.
- Забезпечити єдиний інтерфейс для різних транспортів: cURL, сокети, HTTP-потоки тощо.
- Дозволити легко замінювати транспорт у тестах та розширювати функціонал без зміни коду клієнта.

## Методи

| Метод                                                      | Опис                                                                                                  |
|:-----------------------------------------------------------|:-----------------------------------------------------------------------------------------------------|
| `send(OutboundRequest $request): InboundResponse`          | Відправляє вихідний HTTP-запит і повертає об'єкт `InboundResponse` з результатом виконання.           |
| `isSupported(): bool`                                      | Перевіряє, чи доступна дана реалізація транспорту (наприклад, наявність розширення cURL або функцій сокетів).|

## Приклад реалізації

### 1. Використання cURL
```php
use Scaleum\Http\Client\Transport\TransportInterface;
use Scaleum\Http\Client\Transport\CurlTransport;

class MyCurlClient implements TransportInterface {
    private CurlTransport $transport;

    public function __construct() {
        $this->transport = new CurlTransport();
    }

    public function send(OutboundRequest $request): InboundResponse {
        if (! $this->transport->isSupported()) {
            throw new RuntimeException('cURL unavailable');
        }
        return $this->transport->send($request);
    }

    public function isSupported(): bool {
        return $this->transport->isSupported();
    }
}
```

### 2. Проста «заглушка» для тестів
```php
use Scaleum\Http\Client\Transport\TransportInterface;
use Scaleum\Http\OutboundRequest;
use Scaleum\Http\InboundResponse;

class MockTransport implements TransportInterface {
    private InboundResponse $response;

    public function __construct(InboundResponse $response) {
        $this->response = $response;
    }

    public function send(OutboundRequest $request): InboundResponse {
        // Можна перевірити параметри запиту і повернути заздалегідь сконструйовану відповідь
        return $this->response;
    }

    public function isSupported(): bool {
        return true;
    }
}

// У тесті:
$mockResponse = new InboundResponse(200, ['Content-Type'=>'application/json'], json_encode(['ok'=>true]));
$transport    = new MockTransport($mockResponse);
$result       = $transport->send(new OutboundRequest('GET', new Uri('/test')));
assert($result->getParsedBody()['ok'] === true);
```

### 3. Перевірка підтримки транспорту
```php
foreach ([$curlTransport, $socketTransport] as $t) {
    if ($t->isSupported()) {
        // обираємо перший доступний транспорт
        $response = $t->send($request);
        break;
    }
}
```

[Повернутись до змісту](../../../../index.md)