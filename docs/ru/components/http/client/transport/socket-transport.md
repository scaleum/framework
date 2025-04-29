[Вернуться к оглавлению](../../../../index.md)
# SocketTransport

`SocketTransport` — реализация `TransportInterface` на базе сокетов (fsockopen), отвечающая за отправку `OutboundRequest` и получение `InboundResponse` без использования cURL.

## Назначение

- Отправка произвольных HTTP-запросов (GET, POST, PUT, DELETE, HEAD и др.) на уровне сокетов.
- Формирование первой строки запроса и заголовков вручную.
- Поддержка Basic и Bearer (JWT/OAuth2) авторизации через заголовок `Authorization`.
- Обработка ответов сервера: чтение статусной строки, заголовков, тела и редиректов.
- Асинхронный режим (`isAsync()`) — отправка без ожидания ответа.

## Свойства

| Свойство           | Тип            | Описание                                                                   |
|:------------------|:---------------|:---------------------------------------------------------------------------|
| `protected ?string $authType` | `string\|null`   | Тип авторизации: `BASIC`, `BEARER`, `OAUTH2`, `JWT`.                        |
| `protected ?string $username` | `string\|null`   | Имя пользователя для Basic-авторизации.                                     |
| `protected ?string $password` | `string\|null`   | Пароль для Basic-авторизации.                                               |
| `protected ?string $token`    | `string\|null`   | Токен для Bearer-авторизации.                                               |

## Методы

### isSupported()
```php
public function isSupported(): bool
```
Проверяет наличие функции `fsockopen`. Если сокеты не поддерживаются, возвращает `false`.

### send()
```php
public function send(OutboundRequest $request): InboundResponse
```
1. **Проверка поддержки**:
```php
if (! $this->isSupported()) {
    throw new ERuntimeError('Socket transport is not supported');
}
```

2. **Чтение параметров запроса**:
```php
$url        = (string)$request->getUri();
$method     = strtoupper($request->getMethod());
$headers    = new HeadersManager($request->getHeaders());
$body       = $request->getBody()->isSeekable()
    ? $request->getBody()->rewind() && $request->getBody()->getContents()
    : $request->getBody()->getContents();
```

3. **Парсинг URL** через `UrlHelper::parse` и выбор порта (80 или 443 с SSL).
4. **Открытие сокета**:
```php
$socket = fsockopen($host, $port, $errno, $errstr, $this->getTimeout());
if (! $socket) {
    throw new EHttpException(501, $errstr);
}
```

5. **Установка заголовков** (Content-Type, Content-Length, Host, Connection) и авторизации:
```php
// Basic
echo $headers->setHeader('Authorization', 'Basic '.base64_encode("$user:$pass"));
// Bearer
echo $headers->setHeader('Authorization', "Bearer $token");
```

6. **Формирование запроса**:
```php
$requestLine = sprintf("%s %s HTTP/%.1f\r\n",
    $method,
    $requestPath,
    $request->getProtocolVersion()
);
$raw = $requestLine . implode("\r\n", $headers->getAsStrings()) . "\r\n\r\n" . $body;
fwrite($socket, $raw);
```

7. **Асинхронный режим**:
```php
if ($request->isAsync()) {
    fclose($socket);
    return new InboundResponse();
}
```

8. **Чтение ответа**:
   - Статусная строка и код состояния через `fgets` и `preg_match`.
   - Цикл чтения заголовков до пустой строки.
   - Чтение тела через `stream_get_contents`.
9. **Создание `InboundResponse`** с кодом, заголовками и телом в `Stream`.
10. **Обработка редиректов**: при наличии заголовка `Location` рекурсивный вызов `send()`.

## Примеры использования

### 1. GET-запрос
```php
$transport = new SocketTransport();
$request   = new OutboundRequest('GET', new Uri('/api/status')); // базовый хост и порт берутся из настроек Requester
$response  = $transport->send($request);
echo $response->getStatusCode();     // например, 200
echo $response->getParsedBody();     // тело ответа
```

### 2. POST с JSON
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

### 3. Basic-авторизация
```php
$transport = (new SocketTransport())
    ->setAuthType('BASIC')
    ->setUsername('user')
    ->setPassword('secret');

$request  = new OutboundRequest('GET', new Uri('/private'));
$response = $transport->send($request);
```

### 4. Bearer-авторизация
```php
$transport = (new SocketTransport())
    ->setAuthType('BEARER')
    ->setToken('eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJ1c2VyX2lkIjoxLCJleHBpcnkiOjE2MjIwNzYwMzA');

$request  = new OutboundRequest('DELETE', new Uri('/resource/123'));
$response = $transport->send($request);
```

### 5. Асинхронный запрос
```php
$request = (new OutboundRequest('GET', new Uri('/long-poll')))
    ->setAsync(true);

$transport = new SocketTransport();
// отправка без ожидания ответа
$transport->send($request);
```

### 6. Обработка редиректа
```php
// По умолчанию ограничение редиректов из TransportAbstract
$response = (new SocketTransport())->send(
    new OutboundRequest('GET', new Uri('http://short.url/xyz'))
);
```

[Вернуться к оглавлению](../../../../index.md)

