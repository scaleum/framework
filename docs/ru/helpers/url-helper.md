[Вернуться к оглавлению](../index.md)
# UrlHelper

`UrlHelper` — утилитарный класс для работы с URL в Scaleum Framework.

## Назначение

- Генерация базового URL
- Парсинг URL
- Редиректы

## Основные методы

| Метод | Назначение |
|:------|:-----------|
| `baseUrl(string $url = '')` | Получение базового URL |
| `getServerPort()` | Получение порта сервера |
| `getServerName()` | Получение имени сервера |
| `getServerProtocol()` | Определение протокола |
| `parse(string $url = '')` | Парсинг URL (простой) |
| `parseAlt(string $url = '')` | Парсинг URL (альтернативный) |
| `redirect(string $uri = '', string $method = 'location', int $httpResponseCode = 302)` | Перенаправление |

## Примеры использования

### Получение базового URL

```php
$url = UrlHelper::baseUrl('images/logo.png');
```

### Парсинг URL

```php
$parts = UrlHelper::parse('https://example.com:8080/path/to/file.php?param=value#section');
```

### Парсинг URL (альтернативный)

```php
$parts = UrlHelper::parseAlt('https://user:pass@sub.domain.com/path/to/file.php?foo=bar');
```

### Редирект

```php
UrlHelper::redirect('/login');
```

[Вернуться к оглавлению](../index.md)