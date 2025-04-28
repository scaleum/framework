[Вернуться к оглавлению](../../../../index.md)
# CurlTransport

`CurlTransport` — реализация `TransportInterface` на базе cURL, отвечающая за отправку `OutboundRequest` и получение `InboundResponse`.

## Назначение

- Инициализация и конфигурация cURL-сессии для HTTP-запросов (GET, POST, PUT, DELETE, HEAD и др.).
- Поддержка обработки заголовков, тела запроса и ответа, статуса, редиректов.
- Возможность базовой и Bearer-авторизации, NTLM и Digest.
- Возможность асинхронных запросов (без ожидания ответа).

## Свойства

| Свойство           | Тип          | Описание                                                     |
|:------------------|:-------------|:-------------------------------------------------------------|
| `protected ?string $authType` | `string\|null` | Тип авторизации: BASIC, DIGEST, BEARER, NTLM, ANY, ANYSAFE. |
| `protected ?string $username` | `string\|null` | Имя пользователя для базовой или NTLM-авторизации.           |
| `protected ?string $password` | `string\|null` | Пароль для базовой или NTLM-авторизации.                     |
| `protected ?string $token`    | `string\|null` | Токен для Bearer-авторизации.                                |
| `protected ?string $domain`   | `string\|null` | Домен для NTLM-авторизации.                                  |

## Методы

### send
```php
public function send(OutboundRequest $request): InboundResponse
```
- Проверяет поддержку cURL через `isSupported()`.
- Читает URL, метод, заголовки и тело из `$request`.
- Парсит URL и настраивает опции cURL (`CURLOPT_*`) в зависимости от метода и содержимого.
- Устанавливает заголовки через `CURLOPT_HTTPHEADER`.
- Обрабатывает авторизацию:
  - BASIC, DIGEST, NTLM: `CURLOPT_USERPWD`
  - BEARER: `CURLOPT_XOAUTH2_BEARER`
- Выполняет запрос (`curl_exec`) и обрабатывает ошибки cURL.
- Извлекает HTTP-код, заголовки ответа и тело, формирует `InboundResponse`.
- Обрабатывает редиректы, при необходимости рекурсивно вызывая `send()`.

### isSupported
```php
public function isSupported(): bool
```
- Проверяет наличие функций `curl_init` и `curl_exec`.

### Геттеры/сеттеры авторизации
```php
public function setAuthType(string $authType): static
public function setUsername(string $username): static
public function setPassword(string $password): static
public function setToken(string $token): static
public function setDomain(string $domain): static
```
- Устанавливают параметры для различных схем авторизации.

## Примеры использования

### 1. Простая GET-запрос
```php
$transport = new CurlTransport();
$request   = new OutboundRequest('GET', new Uri('https://api.example.com/data'));
$response  = $transport->send($request);
$data      = $response->getParsedBody();
```

### 2. POST с JSON-данными
```php
$transport = new CurlTransport();
$request   = new OutboundRequest(
    'POST',
    new Uri('https://api.example.com/users'),
    ['Content-Type' => 'application/json'],
    json_encode(['name' => 'Alice', 'age' => 25])
);
$response  = $transport->send($request);
```

### 3. Basic-авторизация
```php
$transport = (new CurlTransport())
    ->setAuthType('BASIC')
    ->setUsername('user')
    ->setPassword('secret');

$request  = new OutboundRequest('GET', new Uri('https://secure.example.com/profile'));
$response = $transport->send($request);
```

### 4. Bearer-авторизация
```php
$transport = (new CurlTransport())
    ->setAuthType('BEARER')
    ->setToken('your-jwt-token');

$request  = new OutboundRequest('DELETE', new Uri('https://api.example.com/resource/42'));
$response = $transport->send($request);
```

### 5. Обработка редиректов
```php
$transport = (new CurlTransport())
    ->setRedirectsCount(3); // разрешить до 3 редиректов

$response = $transport->send(
    new OutboundRequest('GET', new Uri('http://short.url/xyz'))
);
// автоматически перейдёт по Location до 3 раз
```

[Вернуться к оглавлению](../../../../index.md)