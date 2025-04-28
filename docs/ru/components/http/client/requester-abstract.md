[Вернуться к оглавлению](../../../index.md)

# RequesterAbstract

`RequesterAbstract` — базовый абстрактный класс для отправки исходящих HTTP-запросов через различные транспортные механизмы (например, cURL), расширяющий `Hydrator` и предоставляющий общий интерфейс для настройки соединения.

## Назначение

- Хранение и управление настройками HTTP-клиента: протокол (`http`, `https`), хост, порт.
- Отложенная инициализация транспорта через метод `getDefaultClient()`.
- Построение полного URL для запроса через `getRequestUrl()`.
- Абстрактный метод `send()` для реализации логики отправки запроса и получения ответа.

## Свойства

| Свойство                       | Тип                | Описание                         |                                                                      |
| ------------------------------ | ------------------ | -------------------------------- | -------------------------------------------------------------------- |
| `protected string $protocol`   | `string`           | Протокол (по умолчанию `http`).  |                                                                      |
| `protected string $host`       | `string`           | Хост (по умолчанию `localhost`). |                                                                      |
| `protected int $port`          | `int`              | Порт (по умолчанию `80`).        |                                                                      |
| \`protected TransportInterface | null \$transport\` | `TransportInterface` или `null`  | Транспортный клиент; если `null`, используется `getDefaultClient()`. |

## Методы

### getDefaultClient

```php
protected function getDefaultClient(): TransportInterface
```

Возвращает реализацию `TransportInterface` по умолчанию (например, `CurlTransport`). Позволяет переопределить транспорт в дочернем классе.

### getProtocol / setProtocol

```php
public function getProtocol(): string
public function setProtocol(string $value): self
```

- `getProtocol()` — возвращает непустой протокол (`http` по умолчанию).
- `setProtocol()` — нормализует строку (нижний регистр, удаление спецсимволов) и устанавливает `protocol`.

### getHost / setHost

```php
public function getHost(): string
public function setHost(mixed $value): self
```

- `getHost()` — возвращает непустой хост (`localhost` по умолчанию).
- `setHost()` — приводит значение к строке и устанавливает `host`.

### getPort / setPort

```php
public function getPort(): int
public function setPort(mixed $value): self
```

- `getPort()` — возвращает валидный номер порта (`80` по умолчанию).
- `setPort()` — приводит значение к `int` и устанавливает `port`.

### getRequestUrl

```php
public function getRequestUrl(string $url = ''): string
```

- Строит базовый URL: `<protocol>://<host>[:port]`.
- Добавляет `/$url`, если параметр не пуст.
- Пример: `https://api.example.com:8080/v1/users`

### send

```php
abstract public function send(OutboundRequest $request): InboundResponse
```

- Абстрактный метод для отправки `OutboundRequest` и получения `InboundResponse`.
- Реализуется в конкретных подклассах, используя `$this->transport`.

## Примеры

### 1. Реализация подкласса cURL

```php
use Scaleum\Http\Client\RequesterAbstract;
use Scaleum\Http\Client\Transport\TransportInterface;
use Scaleum\Http\OutboundRequest;
use Scaleum\Http\InboundResponse;

class Requester extends RequesterAbstract {
    protected function getDefaultClient(): TransportInterface {
        return new CurlTransport(); // собственный класс cURL
    }

    public function send(OutboundRequest $request): InboundResponse {
        $client = $this->transport ?? $this->getDefaultClient();
        return $client->sendRequest($request);
    }
}
```

### 2. Конфигурация и отправка запроса

```php
$requester = new Requester();
$requester
    ->setProtocol('https')
    ->setHost('api.example.com')
    ->setPort(443);

// Сформирует URL https://api.example.com:443/v1/items?limit=10
$url = $requester->getRequestUrl('v1/items?limit=10');

$request = new OutboundRequest(
    'GET',
    new Uri('/v1/items?limit=10')
);

$response = $requester->send($request);
$data = $response->getParsedBody();
```

### 3. Подмена транспорта

```php
// Можно внедрить MockTransport для тестирования
$mock = new class implements TransportInterface {
    public function send($req) {
        return new InboundResponse(200, ['Content-Type'=>'application/json'], json_encode(['ok'=>true]));
    }
};

$requester = (new Requester())
    ->init(['transport' => $mock]); // метод Hydrator

$response = $requester->send(new OutboundRequest('POST', new Uri('/test'), [], ['foo'=>'bar']));
echo $response->getParsedBody()['ok']; // true
```

[Вернуться к оглавлению](../../../index.md)

