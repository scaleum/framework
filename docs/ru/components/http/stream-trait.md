[Вернуться к оглавлению](../../index.md)
# StreamTrait

`StreamTrait` — вспомогательный трейд для подготовки заголовков и тела HTTP-сообщения. Используется в классах `OutboundRequest` и `OutboundResponse` для конвертации различных типов данных в PSR-7 `StreamInterface` и автоматической установки необходимых заголовков.

## Методы

### prepareHeadersAndStream

```php
protected function prepareHeadersAndStream(array $_headers, mixed $_body): array
```

1. Если `$_body` уже реализует `StreamInterface`, возвращает его без изменений.
2. Инициализирует временный поток:
   ```php
   $stream  = new Stream(fopen('php://temp', 'w+'));
   $headers = new HeadersManager($_headers);
   ```
3. Обработка типов `$_body`:
   - **Массив или объект** → JSON:
     ```php
     $_body = json_encode($_body, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
     $headers->setHeader('Content-Type', 'application/json');
     $headers->setHeader('Content-Length', (string) strlen($_body));
     ```
   - **Путь к файлу** → определение MIME, заголовки для скачивания:
     ```php
     $headers->setHeader('Content-Type', $mimeType);
     $headers->setHeader('Content-Disposition', 'attachment; filename="'.basename($_body).'"');
     $headers->setHeader('Content-Length', (string) filesize($_body));
     $headers->setHeader('Content-Transfer-Encoding', 'binary');
     $headers->setHeader('Last-Modified', gmdate('D, d M Y H:i:s', filemtime($_body)) . ' GMT');
     return [new Stream(fopen($_body, 'r+')), $headers->getAll()];
     ```
   - **Строка** → распознавание типа контента по содержимому:
     ```php
     $headers->setHeader('Content-Type', $this->detectMimeTypeFromContent($_body));
     $headers->setHeader('Content-Length', (string) mb_strlen($_body));
     ```
   - **Прочие типы** → приводятся к строке аналогично строкам.
4. Запись в поток и возврат клонированных заголовков и потока:
   ```php
   $stream->write($_body);
   $stream->rewind();
   return [$headers->getAll(), $stream];
   ```

### detectMimeTypeFromContent

```php
private function detectMimeTypeFromContent(string $content): string
```

Распознаёт MIME по начальным символам:
- JSON: `application/json`
- HTML: `text/html`
- XML: `application/xml`
- PHP-код: `application/x-httpd-php`
- SQL-запросы: `text/sql`
- По умолчанию: `text/plain`

### getMimeType

```php
private function getMimeType(string $filePath): ?string
```

Определяет MIME по расширению файла из предопределённого списка: `.txt`, `.html`, `.css`, `.js`, `.json`, `.xml`, `.csv`, `.sql`, `.pdf`, `.zip`, `.tar`, `.gz`, `.jpg`, `.png`, `.gif`, `.mp3`, `.wav`, `.mp4`.

## Примеры использования

### 1. JSON из массива
```php
$trait = new OutboundRequest('POST', $uri); // или другой класс с использованием трейда
$body = ['foo' => 'bar', 'baz' => [1,2,3]];
list($headers, $stream) = $trait->prepareHeadersAndStream([], $body);
// $headers['Content-Type'] = ['application/json']
// $headers['Content-Length'] = [число байт JSON]
// $stream содержит JSON-строку
```

### 2. Отправка файла
```php
$file = '/path/to/report.pdf';
list($headers, $stream) = $trait->prepareHeadersAndStream([], $file);
// $headers['Content-Type'] = ['application/pdf']
// $headers['Content-Disposition'] = ['attachment; filename="report.pdf"']
// $headers['Content-Length'] = [размер файла]
// $stream — поток файла
```

### 3. Авто-распознавание текстовых данных
```php
$text = "<h1>Title</h1>";
list($headers, $stream) = $trait->prepareHeadersAndStream([], $text);
// $headers['Content-Type'] = ['text/html']
// $headers['Content-Length'] = ['14']
```

### 4. Неизвестный тип (объект)
```php
class Foo { public function __toString() { return 'foo'; }}
$foo = new Foo();
list($headers, $stream) = $trait->prepareHeadersAndStream([], $foo);
// $headers['Content-Type'] = ['text/plain']
// $stream содержит строку 'foo'
```

[Вернуться к оглавлению](../../index.md)