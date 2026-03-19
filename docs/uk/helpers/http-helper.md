[Повернутись до змісту](../index.md)

[EN](../../en/helpers/http-helper.md) | **UK** | [RU](../../ru/helpers/http-helper.md)
# HttpHelper

`HttpHelper` — утилітний клас для роботи з HTTP-заголовками, статусами, IP-адресами та User-Agent.

## Призначення

- Управління HTTP-статусами та заголовками
- Валідація HTTP-методів
- Визначення IP-адреси та User-Agent клієнта

## Основні константи

| Константа | Значення |
|:------------|:------------|
| `METHOD_GET` | GET |
| `METHOD_POST` | POST |
| `METHOD_PUT` | PUT |
| `METHOD_PATCH` | PATCH |
| `METHOD_DELETE` | DELETE |
| `METHOD_OPTIONS` | OPTIONS |
| `METHOD_HEAD` | HEAD |

## Основні методи

| Метод | Призначення |
|:------|:-----------|
| `setHeader` | Встановлення заголовка HTTP |
| `setStatusHeader` | Встановлення HTTP-статусу |
| `getStatusMessage` | Отримання тексту для HTTP-статусу |
| `isStatusCode` | Перевірка коректності HTTP-статусу |
| `isMethod` | Перевірка HTTP-методу |
| `getUserIP` | Отримання IP-адреси користувача |
| `isIpAddress` | Валідація IP-адреси |
| `getUserAgent` | Отримання User-Agent |

## Приклади використання

### Встановлення HTTP-заголовка

```php
HttpHelper::setHeader('Content-Type', 'application/json');
```

### Встановлення HTTP-статусу

```php
HttpHelper::setStatusHeader(404);
```

### Отримання тексту для HTTP-статусу

```php
$statusMessage = HttpHelper::getStatusMessage(200); // "OK"
```

### Перевірка HTTP-статусу

```php
if (HttpHelper::isStatusCode(404)) {
    // Це коректний статус
}
```

### Перевірка HTTP-методу

```php
if (HttpHelper::isMethod('POST')) {
    // POST є допустимим HTTP-методом
}
```

### Отримання IP-адреси користувача

```php
$userIp = HttpHelper::getUserIP();
```

### Валідація IP-адреси

```php
if (HttpHelper::isIpAddress($userIp)) {
    // Адреса коректна
}
```

### Отримання User-Agent

```php
$userAgent = HttpHelper::getUserAgent();
```

[Повернутись до змісту](../index.md)