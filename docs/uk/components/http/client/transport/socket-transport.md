[Повернутись до змісту](../../../../index.md)

[EN](../../../../../en/components/http/client/transport/socket-transport.md) | **UK** | [RU](../../../../../ru/components/http/client/transport/socket-transport.md)
#  SocketTransport

`SocketTransport` — реалізація `TransportInterface` на базі сокетів (fsockopen), що відповідає за відправлення `OutboundRequest` та отримання `InboundResponse` без використання cURL.

##  Призначення

- Відправлення довільних HTTP-запитів (GET, POST, PUT, DELETE, HEAD тощо) на рівні сокетів.
- Формування першого рядка запиту та заголовків вручну.
- Підтримка Basic та Bearer (JWT/OAuth2) авторизації через заголовок `Authorization`.
- Обробка відповідей сервера: читання статусного рядка, заголовків, тіла та редиректів.
- Асинхронний режим (`isAsync()`) — відправлення без очікування відповіді.

##  Властивості

| Властивість           | Тип            | Опис                                                                   |
|:---------------------|:---------------|:----------------------------------------------------------------------|
| `protected ?string $authType` | `string\|null`   | Тип авторизації: `BASIC`, `BEARER`, `OAUTH2`, `JWT`.                  |
| `protected ?string $username` | `string\|null`   | Ім'я користувача для Basic-авторизації.                               |
| `protected ?string $password` | `string\|null`   | Пароль для Basic-авторизації.                                         |
| `protected ?string $token`    | `string\|null`   | Токен для Bearer-авторизації.                                         |

##  Методи

###  isSupported()
```php
public function isSupported(): bool
```
Перевіряє наявність функції `fsockopen`. Якщо сокети не підтримуються, повертає `false`.

###  send()
```php
public function send(OutboundRequest $request): InboundResponse
```
1. **Перевірка підтримки**:
```php
if (! $this->isSupported()) {
    throw new ERuntimeError('Socket transport is not supported');
}
```

2. **Читання параметрів запиту**:
```php
$url        = (string)$request->getUri();
$method     = strtoupper($request->getMethod());
$headers    = new HeadersManager($request->getHeaders());
$body       = $request->getBody()->isSeekable()
    ? $request->getBody()->rewind() && $request->getBody()->getContents()
    : $request->getBody()->getContents();
```

3. **Парсинг URL** через `UrlHelper::parse` та вибір порту (80 або 443 з SSL).
4. **Відкриття сокета**:
```php
$socket = fsockopen($host, $port, $errno, $errstr, $this->getTimeout());
if (! $socket) {
    throw new EHttpException(501, $errstr);
}
```

5. **Встановлення заголовків** (Content-Type, Content-Length, Host, Connection) та авторизації:
```php
// Basic
echo $headers->setHeader('Authorization', 'Basic '.base64_encode("$user:$pass"));
// Bearer
echo $headers->setHeader('Authorization', "Bearer $token");
```

6. **Формування запиту**:
```php
$requestLine = sprintf("%s %s HTTP/%.1f\r\n",
    $method,
    $requestPath,
    $request->getProtocolVersion()
);
$raw = $requestLine . implode("\r\n", $headers->getAsStrings()) . "\r\n\r\n" . $body;
fwrite($socket, $raw);
```

7. **Асинхронний режим**:
```php
if ($request->isAsync()) {
    fclose($socket);
    return new InboundResponse();
}
```

8. **Читання відповіді**:
   - Статусний рядок і код стану через `fgets` і `preg_match`.
   - Цикл читання заголовків до порожнього рядка.
   - Читання тіла через `stream_get_contents`.
9. **Створення `InboundResponse`** з кодом, заголовками та тілом у `Stream`.
10. **Обробка редиректів**: при наявності заголовка `Location` рекурсивний виклик `send()`.

##  Приклади використання

###  1. GET-запит
```php
$transport = new SocketTransport();
$request   = new OutboundRequest('GET', new Uri('/api/status')); // базовий хост і порт беруться з налаштувань Requester
$response  = $transport->send($request);
echo $response->getStatusCode();     // наприклад, 200
echo $response->getParsedBody();     // тіло відповіді
```

###  2. POST з JSON
```php
$transport = new SocketTransport();
$data      = ['name' => 'Bob', 'age' => 28];
$request   = new OutboundRequest(
    'POST',
    new Uri('/users'),
    ['Content-Type' => 'application/json'],
    json_encode($data)
);
$response  = $transport->send($request);
```

###  3. Basic-авторизація
```php
$transport = (new SocketTransport())
    ->setAuthType('BASIC')
    ->setUsername('user')
    ->setPassword('secret');

$request  = new OutboundRequest('GET', new Uri('/private'));
$response = $transport->send($request);
```

###  4. Bearer-авторизація
```php
$transport = (new SocketTransport())
    ->setAuthType('BEARER')
    ->setToken('eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJ1c2VyX2lkIjoxLCJleHBpcnkiOjE2MjIwNzYwMzA');

$request  = new OutboundRequest('DELETE', new Uri('/resource/123'));
$response = $transport->send($request);
```

###  5. Асинхронний запит
```php
$request = (new OutboundRequest('GET', new Uri('/long-poll')))
    ->setAsync(true);

$transport = new SocketTransport();
// відправлення без очікування відповіді
$transport->send($request);
```

###  6. Обробка редиректу
```php
// За замовчуванням обмеження редиректів з TransportAbstract
$response = (new SocketTransport())->send(
    new OutboundRequest('GET', new Uri('http://short.url/xyz'))
);
```

[Повернутися до змісту](../../../../index.md)

