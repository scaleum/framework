[Вернутися до змісту](../../../index.md)

[EN](../../../../en/components/http/client/requester-abstract.md) | **UK** | [RU](../../../../ru/components/http/client/requester-abstract.md)
# RequesterAbstract

`RequesterAbstract` — базовий абстрактний клас для відправки вихідних HTTP-запитів через різні транспортні механізми (наприклад, cURL), що розширює `Hydrator` і надає загальний інтерфейс для налаштування з'єднання.

## Призначення

- Зберігання та керування налаштуваннями HTTP-клієнта: протокол (`http`, `https`), хост, порт.
- Відкладена ініціалізація транспорту через метод `getDefaultClient()`.
- Побудова повного URL для запиту через `getRequestUrl()`.
- Абстрактний метод `send()` для реалізації логіки відправки запиту та отримання відповіді.

## Властивості

| Властивість                    | Тип                | Опис                            |
| ------------------------------ | ------------------ | ------------------------------- |
| `protected string $protocol`   | `string`           | Протокол (за замовчуванням `http`)  |
| `protected string $host`       | `string`           | Хост (за замовчуванням `localhost`) |
| `protected int $port`          | `int`              | Порт (за замовчуванням `80`)        |
| `protected TransportInterface $transport` | `TransportInterface` або `null`  | Транспортний клієнт; якщо `null`, використовується `getDefaultClient()` |

## Методи

### getDefaultClient

```php
protected function getDefaultClient(): TransportInterface
```

Повертає реалізацію `TransportInterface` за замовчуванням (наприклад, `CurlTransport`). Дозволяє перевизначити транспорт у дочірньому класі.

### getProtocol / setProtocol

```php
public function getProtocol(): string
public function setProtocol(string $value): self
```

- `getProtocol()` — повертає непорожній протокол (`http` за замовчуванням).
- `setProtocol()` — нормалізує рядок (нижній регістр, видалення спецсимволів) і встановлює `protocol`.

### getHost / setHost

```php
public function getHost(): string
public function setHost(mixed $value): self
```

- `getHost()` — повертає непорожній хост (`localhost` за замовчуванням).
- `setHost()` — приводить значення до рядка і встановлює `host`.

### getPort / setPort

```php
public function getPort(): int
public function setPort(mixed $value): self
```

- `getPort()` — повертає валідний номер порту (`80` за замовчуванням).
- `setPort()` — приводить значення до `int` і встановлює `port`.

### getRequestUrl

```php
public function getRequestUrl(string $url = ''): string
```

- Будує базовий URL: `<protocol>://<host>[:port]`.
- Додає `/$url`, якщо параметр не порожній.
- Приклад: `https://api.example.com:8080/v1/users`

### send

```php
abstract public function send(OutboundRequest $request): InboundResponse
```

- Абстрактний метод для відправки `OutboundRequest` і отримання `InboundResponse`.
- Реалізується у конкретних підкласах, використовуючи `$this->transport`.

## Приклади

### 1. Реалізація підкласу cURL

```php
use Scaleum\Http\Client\RequesterAbstract;
use Scaleum\Http\Client\Transport\TransportInterface;
use Scaleum\Http\OutboundRequest;
use Scaleum\Http\InboundResponse;

class Requester extends RequesterAbstract {
    protected function getDefaultClient(): TransportInterface {
        return new CurlTransport(); // власний клас cURL
    }

    public function send(OutboundRequest $request): InboundResponse {
        $client = $this->transport ?? $this->getDefaultClient();
        return $client->sendRequest($request);
    }
}
```

### 2. Конфігурація та відправка запиту

```php
$requester = new Requester();
$requester
    ->setProtocol('https')
    ->setHost('api.example.com')
    ->setPort(443);

// Сформує URL https://api.example.com:443/v1/items?limit=10
$url = $requester->getRequestUrl('v1/items?limit=10');

$request = new OutboundRequest(
    'GET',
    new Uri('/v1/items?limit=10')
);

$response = $requester->send($request);
$data = $response->getParsedBody();
```

### 3. Замінювання транспорту

```php
// Можна впровадити MockTransport для тестування
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

[Вернутися до змісту](../../../index.md)