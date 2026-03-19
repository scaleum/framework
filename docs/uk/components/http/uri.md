[Повернутись до змісту](../../index.md)

[EN](../../../en/components/http/uri.md) | **UK** | [RU](../../../ru/components/http/uri.md)
# Uri

`Uri` — клас для роботи з URI в рамках PSR-7, що реалізує `UriInterface`. Дозволяє розбирати, змінювати та будувати рядок URI, а також автоматично підставляти поточний запит, якщо URI не передано.

## Призначення

- Парсинг рядка URI на компоненти: схема, користувацька інформація, хост, порт, шлях, запит, фрагмент.
- Побудова змін через PSR-7 методи `withScheme`, `withHost` тощо, повертаючи клон об’єкта.
- Отримання повного рядка URI через метод `__toString()`.
- Автоматичне визначення поточного URI за відсутності аргументу в конструкторі.

## Конструктор

```php
public function __construct(?string $uri = null)
```

- `$uri` — рядок URI; якщо `null`, буде витягнутий із глобальних змінних (`$_SERVER['REQUEST_URI']`, `PATH_INFO`, `QUERY_STRING`, `$_GET`).
- Очищує невидимі символи (`StringHelper::cleanInvisibleChars`).
- Розбирає URI функцією `parse_url`.
- Заповнює властивості:
  - `$scheme`   — схема (наприклад, `http`, `https`).
  - `$userInfo` — `user[:pass]`.
  - `$host`     — доменне ім’я.
  - `$port`     — порт або `null`.
  - `$path`     — шлях після хоста.
  - `$query`    — рядок запиту без `?`.
  - `$fragment` — фрагмент без `#`.

## Властивості та методи PSR-7

| Метод                             | Опис                                                        |
|:----------------------------------|:------------------------------------------------------------|
| `getScheme(): string`             | Повертає схему.                                             |
| `withScheme(string $scheme): static` | Клонує та встановлює схему.                              |
| `getAuthority(): string`          | Повертає `[userInfo@]host[:port]`.                          |
| `getUserInfo(): string`           | Повертає `userInfo`.                                        |
| `withUserInfo(string $user, ?string $password = null): static` | Встановлює `user:password`.          |
| `getHost(): string`               | Повертає хост.                                              |
| `withHost(string $host): static`  | Клонує та встановлює хост.                                 |
| `getPort(): ?int`                 | Повертає порт або `null`.                                   |
| `withPort(?int $port): static`    | Клонує та встановлює порт.                                 |
| `getPath(): string`               | Повертає шлях.                                              |
| `withPath(string $path): static`  | Клонує та встановлює шлях.                                 |
| `getQuery(): string`              | Повертає рядок запиту.                                      |
| `withQuery(string $query): static`| Клонує та встановлює рядок запиту.                         |
| `getFragment(): string`           | Повертає фрагмент.                                          |
| `withFragment(string $fragment): static` | Клонує та встановлює фрагмент.                        |
| `__toString(): string`            | Складає повний рядок URI з усіх частин.                    |

## Приклад використання

### 1. Розбір статичного URI
```php
use Scaleum\Http\Uri;

$uri = new Uri('https://user:pass@example.com:8080/path/to/resource?foo=1&bar=2#section');
echo $uri->getScheme();      // 'https'
echo $uri->getAuthority();   // 'user:pass@example.com:8080'
echo $uri->getHost();        // 'example.com'
echo $uri->getPort();        // 8080
echo $uri->getPath();        // '/path/to/resource'
echo $uri->getQuery();       // 'foo=1&bar=2'
echo $uri->getFragment();    // 'section'
```

### 2. Модифікація URI через методи PSR-7
```php
$baseUri = new Uri('http://example.com/articles?page=1');

// Переключити на HTTPS і змінити сторінку
$secureUri = $baseUri
    ->withScheme('https')
    ->withQuery('page=2')
    ->withFragment('comments');

echo (string) $secureUri;  // 'https://example.com/articles?page=2#comments'
```

### 3. Отримання поточного запиту у веб-додатку
```php
// припустимо, клієнт запросив '/blog/post/42?preview=true'
$currentUri = new Uri();
// аналогічно: new Uri($_SERVER['REQUEST_URI'])
echo (string) $currentUri; // '/blog/post/42?preview=true'
```

### 4. Робота з користувацькою інформацією
```php
$uri = (new Uri('http://example.com'))->withUserInfo('admin', 'secret');
echo $uri->getAuthority(); // 'admin:secret@example.com'
```

[Повернутись до змісту](../../index.md)