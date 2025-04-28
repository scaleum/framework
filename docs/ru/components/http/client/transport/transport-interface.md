[Вернуться к оглавлению](../../../../index.md)
# TransportInterface

`TransportInterface` — интерфейс для транспортных механизмов HTTP-клиента во фреймворке Scaleum. Определяет контракт для отправки `OutboundRequest` и проверки поддержки транспорта.

## Назначение

- Абстрагировать детали низкоуровневой реализации отправки HTTP-запросов.
- Обеспечить единый интерфейс для различных транспортов: cURL, сокеты, HTTP-потоки и т.п.
- Позволить легко подменять транспорт в тестах и расширять функционал без изменения кода клиента.

## Методы

| Метод                                                      | Описание                                                                                                  |
|:-----------------------------------------------------------|:----------------------------------------------------------------------------------------------------------|
| `send(OutboundRequest $request): InboundResponse`          | Отправляет исходящий HTTP-запрос и возвращает объект `InboundResponse` с результатом выполнения.            |
| `isSupported(): bool`                                      | Проверяет, доступна ли данная реализация транспорта (например, наличие расширения cURL или функций сокетов).|

## Пример реализации

### 1. Использование cURL
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

### 2. Простая «заглушка» для тестов
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
        // Можно проверить параметры запроса и вернуть заранее сконструированный ответ
        return $this->response;
    }

    public function isSupported(): bool {
        return true;
    }
}

// В тесте:
$mockResponse = new InboundResponse(200, ['Content-Type'=>'application/json'], json_encode(['ok'=>true]));
$transport    = new MockTransport($mockResponse);
$result       = $transport->send(new OutboundRequest('GET', new Uri('/test')));
assert($result->getParsedBody()['ok'] === true);
```

### 3. Проверка поддержки транспорта
```php
foreach ([$curlTransport, $socketTransport] as $t) {
    if ($t->isSupported()) {
        // выбираем первый доступный транспорт
        $response = $t->send($request);
        break;
    }
}
```

[Вернуться к оглавлению](../../../../index.md)