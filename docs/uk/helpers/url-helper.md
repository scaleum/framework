[Повернутись до змісту](../index.md)

[EN](../../en/helpers/url-helper.md) | **UK** | [RU](../../ru/helpers/url-helper.md)
# UrlHelper

`UrlHelper` — утилітарний клас для роботи з URL у Scaleum Framework.

## Призначення

- Генерація базового URL
- Парсинг URL
- Редіректи

## Основні методи

| Метод | Призначення |
|:------|:------------|
| `baseUrl(string $url = '')` | Отримання базового URL |
| `getServerPort()` | Отримання порту сервера |
| `getServerName()` | Отримання імені сервера |
| `getServerProtocol()` | Визначення протоколу |
| `parse(string $url = '')` | Парсинг URL (простіший) |
| `parseAlt(string $url = '')` | Парсинг URL (альтернативний) |
| `redirect(string $uri = '', string $method = 'location', int $httpResponseCode = 302)` | Перенаправлення |

## Приклади використання

### Отримання базового URL

```php
$url = UrlHelper::baseUrl('images/logo.png');
```

### Парсинг URL

```php
$parts = UrlHelper::parse('https://example.com:8080/path/to/file.php?param=value#section');
```

### Парсинг URL (альтернативний)

```php
$parts = UrlHelper::parseAlt('https://user:pass@sub.domain.com/path/to/file.php?foo=bar');
```

### Редірект

```php
UrlHelper::redirect('/login');
```

[Повернутись до змісту](../index.md)