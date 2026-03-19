[Повернутись до змісту](../../index.md)

[EN](../../../en/components/http/stream-trait.md) | **UK** | [RU](../../../ru/components/http/stream-trait.md)
# StreamTrait

`StreamTrait` — допоміжний трейд для підготовки заголовків і тіла HTTP-повідомлення. Використовується в класах `OutboundRequest` і `OutboundResponse` для конвертації різних типів даних у PSR-7 `StreamInterface` та автоматичного встановлення необхідних заголовків.

## Методи

### prepareHeadersAndStream

```php
protected function prepareHeadersAndStream(array $_headers, mixed $_body): array
```

1. Якщо `$_body` вже реалізує `StreamInterface`, повертає його без змін.
2. Ініціалізує тимчасовий потік:
   ```php
   $stream  = new Stream(fopen('php://temp', 'w+'));
   $headers = new HeadersManager($_headers);
   ```
3. Обробка типів `$_body`:
   - **Масив або об'єкт** → JSON:
     ```php
     $_body = json_encode($_body, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
     $headers->setHeader('Content-Type', 'application/json');
     $headers->setHeader('Content-Length', (string) strlen($_body));
     ```
   - **Шлях до файлу** → визначення MIME, заголовки для завантаження:
     ```php
     $headers->setHeader('Content-Type', $mimeType);
     $headers->setHeader('Content-Disposition', 'attachment; filename="'.basename($_body).'"');
     $headers->setHeader('Content-Length', (string) filesize($_body));
     $headers->setHeader('Content-Transfer-Encoding', 'binary');
     $headers->setHeader('Last-Modified', gmdate('D, d M Y H:i:s', filemtime($_body)) . ' GMT');
     return [new Stream(fopen($_body, 'r+')), $headers->getAll()];
     ```
   - **Рядок** → розпізнавання типу контенту за вмістом:
     ```php
     $headers->setHeader('Content-Type', $this->detectMimeTypeFromContent($_body));
     $headers->setHeader('Content-Length', (string) mb_strlen($_body));
     ```
   - **Інші типи** → приводяться до рядка аналогічно рядкам.
4. Запис у потік і повернення клонованих заголовків і потоку:
   ```php
   $stream->write($_body);
   $stream->rewind();
   return [$headers->getAll(), $stream];
   ```

### detectMimeTypeFromContent

```php
private function detectMimeTypeFromContent(string $content): string
```

Розпізнає MIME за початковими символами:
- JSON: `application/json`
- HTML: `text/html`
- XML: `application/xml`
- PHP-код: `application/x-httpd-php`
- SQL-запити: `text/sql`
- За замовчуванням: `text/plain`

### getMimeType

```php
private function getMimeType(string $filePath): ?string
```

Визначає MIME за розширенням файлу зі заздалегідь визначеного списку: `.txt`, `.html`, `.css`, `.js`, `.json`, `.xml`, `.csv`, `.sql`, `.pdf`, `.zip`, `.tar`, `.gz`, `.jpg`, `.png`, `.gif`, `.mp3`, `.wav`, `.mp4`.

## Приклади використання

### 1. JSON з масиву
```php
$trait = new OutboundRequest('POST', $uri); // або інший клас з використанням трейда
$body = ['foo' => 'bar', 'baz' => [1,2,3]];
list($headers, $stream) = $trait->prepareHeadersAndStream([], $body);
// $headers['Content-Type'] = ['application/json']
// $headers['Content-Length'] = [число байт JSON]
// $stream містить JSON-рядок
```

### 2. Відправка файлу
```php
$file = '/path/to/report.pdf';
list($headers, $stream) = $trait->prepareHeadersAndStream([], $file);
// $headers['Content-Type'] = ['application/pdf']
// $headers['Content-Disposition'] = ['attachment; filename="report.pdf"']
// $headers['Content-Length'] = [розмір файлу]
// $stream — потік файлу
```

### 3. Авто-розпізнавання текстових даних
```php
$text = "<h1>Title</h1>";
list($headers, $stream) = $trait->prepareHeadersAndStream([], $text);
// $headers['Content-Type'] = ['text/html']
// $headers['Content-Length'] = ['14']
```

### 4. Невідомий тип (об'єкт)
```php
class Foo { public function __toString() { return 'foo'; }}
$foo = new Foo();
list($headers, $stream) = $trait->prepareHeadersAndStream([], $foo);
// $headers['Content-Type'] = ['text/plain']
// $stream містить рядок 'foo'
```

[Повернутись до змісту](../../index.md)