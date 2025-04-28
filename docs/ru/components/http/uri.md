[Вернуться к оглавлению](../../index.md)
# Uri

`Uri` — класс для работы с URI в рамках PSR-7, реализующий `UriInterface`. Позволяет разбирать, изменять и строить строку URI, а также автоматически подставлять текущий запрос, если URI не передан.

## Назначение

- Парсинг строки URI на компоненты: схема, пользовательская информация, хост, порт, путь, запрос, фрагмент.
- Построение изменений через PSR-7 методы `withScheme`, `withHost` и др., возвращая клон объекта.
- Получение полной строки URI через метод `__toString()`.
- Автоматическое определение текущего URI при отсутствии аргумента в конструкторе.

## Конструктор

```php
public function __construct(?string $uri = null)
```

- `$uri` — строка URI; если `null`, будет извлечён из глобальных переменных (`$_SERVER['REQUEST_URI']`, `PATH_INFO`, `QUERY_STRING`, `$_GET`).
- Очищает невидимые символы (`StringHelper::cleanInvisibleChars`).
- Разбирает URI функцией `parse_url`.
- Заполняет свойства:
  - `$scheme`   — схема (например, `http`, `https`).
  - `$userInfo` — `user[:pass]`.
  - `$host`     — доменное имя.
  - `$port`     — порт или `null`.
  - `$path`     — путь после хоста.
  - `$query`    — строка запроса без `?`.
  - `$fragment` — фрагмент без `#`.

## Свойства и методы PSR-7

| Метод                             | Описание                                                    |
|:----------------------------------|:------------------------------------------------------------|
| `getScheme(): string`             | Возвращает схему.                                           |
| `withScheme(string $scheme): static` | Клонирует и устанавливает схему.                         |
| `getAuthority(): string`          | Возвращает `[userInfo@]host[:port]`.                        |
| `getUserInfo(): string`           | Возвращает `userInfo`.                                      |
| `withUserInfo(string $user, ?string $password = null): static` | Устанавливает `user:password`.    |
| `getHost(): string`               | Возвращает хост.                                            |
| `withHost(string $host): static`  | Клонирует и устанавливает хост.                             |
| `getPort(): ?int`                 | Возвращает порт или `null`.                                 |
| `withPort(?int $port): static`    | Клонирует и устанавливает порт.                             |
| `getPath(): string`               | Возвращает путь.                                            |
| `withPath(string $path): static`  | Клонирует и устанавливает путь.                             |
| `getQuery(): string`              | Возвращает строку запроса.                                  |
| `withQuery(string $query): static`| Клонирует и устанавливает строку запроса.                   |
| `getFragment(): string`           | Возвращает фрагмент.                                        |
| `withFragment(string $fragment): static` | Клонирует и устанавливает фрагмент.                |
| `__toString(): string`            | Составляет полную строку URI из всех частей.                |

## Пример использования

### 1. Разбор статического URI
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

### 2. Модификация URI через методы PSR-7
```php
$baseUri = new Uri('http://example.com/articles?page=1');

// Переключить на HTTPS и изменить страницу
$secureUri = $baseUri
    ->withScheme('https')
    ->withQuery('page=2')
    ->withFragment('comments');

echo (string) $secureUri;  // 'https://example.com/articles?page=2#comments'
```

### 3. Получение текущего запроса в веб-приложении
```php
// допустим, клиент запросил '/blog/post/42?preview=true'
$currentUri = new Uri();
// аналогично: new Uri($_SERVER['REQUEST_URI'])
echo (string) $currentUri; // '/blog/post/42?preview=true'
```

### 4. Работа с пользовательской информацией
```php
$uri = (new Uri('http://example.com'))->withUserInfo('admin', 'secret');
echo $uri->getAuthority(); // 'admin:secret@example.com'
```

[Вернуться к оглавлению](../../index.md)