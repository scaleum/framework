[Повернутись до змісту](../../../../index.md)

[EN](../../../../../en/components/http/client/transport/curl-transport.md) | **UK** | [RU](../../../../../ru/components/http/client/transport/curl-transport.md)
# CurlTransport

`CurlTransport` — реалізація `TransportInterface` на базі cURL, що відповідає за відправку `OutboundRequest` та отримання `InboundResponse`.

## Призначення

- Ініціалізація та конфігурація cURL-сесії для HTTP-запитів (GET, POST, PUT, DELETE, HEAD та ін.).
- Підтримка обробки заголовків, тіла запиту та відповіді, статусу, редиректів.
- Можливість базової та Bearer-авторизації, NTLM і Digest.
- Можливість асинхронних запитів (без очікування відповіді).

## Властивості

| Властивість           | Тип          | Опис                                                         |
|:---------------------|:-------------|:-------------------------------------------------------------|
| `protected ?string $authType` | `string\|null` | Тип авторизації: BASIC, DIGEST, BEARER, NTLM, ANY, ANYSAFE.  |
| `protected ?string $username` | `string\|null` | Ім'я користувача для базової або NTLM-авторизації.           |
| `protected ?string $password` | `string\|null` | Пароль для базової або NTLM-авторизації.                     |
| `protected ?string $token`    | `string\|null` | Токен для Bearer-авторизації.                                |
| `protected ?string $domain`   | `string\|null` | Домен для NTLM-авторизації.                                  |
| `protected bool $verifyPeer`  | `bool`         | Вмикає перевірку TLS-сертифіката. За замовчуванням `false`.   |
| `protected int $verifyHost`   | `int`          | Вмикає перевірку імені хоста в TLS-сертифікаті. За замовчуванням `0`. |

## Методи

### send
```php
public function send(OutboundRequest $request): InboundResponse
```
- Перевіряє підтримку cURL через `isSupported()`.
- Зчитує URL, метод, заголовки та тіло з `$request`.
- Парсить URL і налаштовує опції cURL (`CURLOPT_*`) залежно від методу та вмісту.
- Встановлює заголовки через `CURLOPT_HTTPHEADER`.
- Обробляє авторизацію:
  - BASIC, DIGEST, NTLM: `CURLOPT_USERPWD`
  - BEARER: `CURLOPT_XOAUTH2_BEARER`
- Виконує запит (`curl_exec`) та обробляє помилки cURL.
- Витягує HTTP-код, заголовки відповіді та тіло, формує `InboundResponse`.
- Обробляє редиректи, за потреби рекурсивно викликаючи `send()`.

### isSupported
```php
public function isSupported(): bool
```
- Перевіряє наявність функцій `curl_init` та `curl_exec`.

### Геттери/сеттери авторизації
```php
public function setAuthType(string $authType): static
public function setUsername(string $username): static
public function setPassword(string $password): static
public function setToken(string $token): static
public function setDomain(string $domain): static
```
- Встановлюють параметри для різних схем авторизації.

### Геттери/сеттери перевірки TLS
```php
public function getVerifyPeer(): bool
public function setVerifyPeer(mixed $value): static
public function getVerifyHost(): int
public function setVerifyHost(int $value): static
```
- `verifyPeer` передається до `CURLOPT_SSL_VERIFYPEER`.
- `verifyHost` передається до `CURLOPT_SSL_VERIFYHOST`.
- Поточні значення за замовчуванням, `false` і `0`, вимикають перевірку TLS-сертифіката та імені хоста.
- Для HTTPS-запитів у production слід використовувати `true` і `2`. Вимкнення будь-якої з перевірок робить з'єднання вразливим до атак «людина посередині» та припустиме лише в контрольованому середовищі розробки.

## Приклади використання

### 1. Простий GET-запит
```php
$transport = new CurlTransport();
$request   = new OutboundRequest('GET', new Uri('https://api.example.com/data'));
$response  = $transport->send($request);
$data      = $response->getParsedBody();
```

### 2. POST з JSON-даними
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

### 3. Basic-авторизація
```php
$transport = (new CurlTransport())
    ->setAuthType('BASIC')
    ->setUsername('user')
    ->setPassword('secret');

$request  = new OutboundRequest('GET', new Uri('https://secure.example.com/profile'));
$response = $transport->send($request);
```

### 4. Bearer-авторизація
```php
$transport = (new CurlTransport())
    ->setAuthType('BEARER')
    ->setToken('your-jwt-token');

$request  = new OutboundRequest('DELETE', new Uri('https://api.example.com/resource/42'));
$response = $transport->send($request);
```

### 5. Обробка редиректів
```php
$transport = (new CurlTransport())
    ->setRedirectsCount(3); // дозволити до 3 редиректів

$response = $transport->send(
    new OutboundRequest('GET', new Uri('http://short.url/xyz'))
);
// автоматично перейде за Location до 3 разів
```

### 6. Перевірка TLS
```php
$transport = new CurlTransport([
    'verifyPeer' => true,
    'verifyHost' => 2,
]);
```

Ці параметри також можна налаштувати після створення транспорту:

```php
$transport
    ->setVerifyPeer(true)
    ->setVerifyHost(2);
```

[Повернутись до змісту](../../../../index.md)
