[Вернуться к оглавлению](../index.md)
# HttpHelper

`HttpHelper` — утилитарный класс для работы с HTTP-заголовками, статусами, IP-адресами и User-Agent.

## Назначение

- Управление HTTP-статусами и заголовками
- Валидация HTTP-методов
- Определение IP-адреса и User-Agent клиента

## Основные константы

| Константа | Значение |
|:------------|:------------|
| `METHOD_GET` | GET |
| `METHOD_POST` | POST |
| `METHOD_PUT` | PUT |
| `METHOD_PATCH` | PATCH |
| `METHOD_DELETE` | DELETE |
| `METHOD_OPTIONS` | OPTIONS |
| `METHOD_HEAD` | HEAD |

## Основные методы

| Метод | Назначение |
|:------|:-----------|
| `setHeader` | Установка заголовка HTTP |
| `setStatusHeader` | Установка HTTP-статуса |
| `getStatusMessage` | Получение текста для HTTP-статуса |
| `isStatusCode` | Проверка корректности HTTP-статуса |
| `isMethod` | Проверка HTTP-метода |
| `getUserIP` | Получение IP-адреса пользователя |
| `isIpAddress` | Валидация IP-адреса |
| `getUserAgent` | Получение User-Agent |

## Примеры использования

### Установка HTTP-заголовка

```php
HttpHelper::setHeader('Content-Type', 'application/json');
```

### Установка HTTP-статуса

```php
HttpHelper::setStatusHeader(404);
```

### Получение текста для HTTP-статуса

```php
$statusMessage = HttpHelper::getStatusMessage(200); // "OK"
```

### Проверка HTTP-статуса

```php
if (HttpHelper::isStatusCode(404)) {
    // Это корректный статус
}
```

### Проверка HTTP-метода

```php
if (HttpHelper::isMethod('POST')) {
    // POST является допустимым HTTP-методом
}
```

### Получение IP-адреса пользователя

```php
$userIp = HttpHelper::getUserIP();
```

### Валидация IP-адреса

```php
if (HttpHelper::isIpAddress($userIp)) {
    // Адрес корректный
}
```

### Получение User-Agent

```php
$userAgent = HttpHelper::getUserAgent();
```

[Вернуться к оглавлению](../index.md)