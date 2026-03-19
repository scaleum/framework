[Back to Contents](../../index.md)

**EN** | [UK](../../../uk/components/http/stream-trait.md) | [RU](../../../ru/components/http/stream-trait.md)
#  StreamTrait

`StreamTrait` is a helper trait for preparing headers and the body of an HTTP message. It is used in the classes `OutboundRequest` and `OutboundResponse` to convert various data types into a PSR-7 `StreamInterface` and automatically set the necessary headers.

##  Methods

###  prepareHeadersAndStream

```php
protected function prepareHeadersAndStream(array $_headers, mixed $_body): array
```

1. If `$_body` already implements `StreamInterface`, it returns it unchanged.
2. Initializes a temporary stream:
   ```php
   $stream  = new Stream(fopen('php://temp', 'w+'));
   $headers = new HeadersManager($_headers);
   ```
3. Handling of `$_body` types:
   - **Array or object** → JSON:
     ```php
     $_body = json_encode($_body, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
     $headers->setHeader('Content-Type', 'application/json');
     $headers->setHeader('Content-Length', (string) strlen($_body));
     ```
   - **File path** → MIME detection, headers for download:
     ```php
     $headers->setHeader('Content-Type', $mimeType);
     $headers->setHeader('Content-Disposition', 'attachment; filename="'.basename($_body).'"');
     $headers->setHeader('Content-Length', (string) filesize($_body));
     $headers->setHeader('Content-Transfer-Encoding', 'binary');
     $headers->setHeader('Last-Modified', gmdate('D, d M Y H:i:s', filemtime($_body)) . ' GMT');
     return [new Stream(fopen($_body, 'r+')), $headers->getAll()];
     ```
   - **String** → content type detection by content:
     ```php
     $headers->setHeader('Content-Type', $this->detectMimeTypeFromContent($_body));
     $headers->setHeader('Content-Length', (string) mb_strlen($_body));
     ```
   - **Other types** → cast to string similarly to strings.
4. Write to the stream and return cloned headers and stream:
   ```php
   $stream->write($_body);
   $stream->rewind();
   return [$headers->getAll(), $stream];
   ```

###  detectMimeTypeFromContent

```php
private function detectMimeTypeFromContent(string $content): string
```

Detects MIME type by initial characters:
- JSON: `application/json`
- HTML: `text/html`
- XML: `application/xml`
- PHP code: `application/x-httpd-php`
- SQL queries: `text/sql`
- Default: `text/plain`

###  getMimeType

```php
private function getMimeType(string $filePath): ?string
```

Determines MIME type by file extension from a predefined list: `.txt`, `.html`, `.css`, `.js`, `.json`, `.xml`, `.csv`, `.sql`, `.pdf`, `.zip`, `.tar`, `.gz`, `.jpg`, `.png`, `.gif`, `.mp3`, `.wav`, `.mp4`.

##  Usage Examples

###  1. JSON from array
```php
$trait = new OutboundRequest('POST', $uri); // or another class using the trait
$body = ['foo' => 'bar', 'baz' => [1,2,3]];
list($headers, $stream) = $trait->prepareHeadersAndStream([], $body);
// $headers['Content-Type'] = ['application/json']
// $headers['Content-Length'] = [number of JSON bytes]
// $stream contains the JSON string
```

###  2. Sending a file
```php
$file = '/path/to/report.pdf';
list($headers, $stream) = $trait->prepareHeadersAndStream([], $file);
// $headers['Content-Type'] = ['application/pdf']
// $headers['Content-Disposition'] = ['attachment; filename="report.pdf"']
// $headers['Content-Length'] = [file size]
// $stream — file stream
```

###  3. Auto-detection of text data
```php
$text = "<h1>Title</h1>";
list($headers, $stream) = $trait->prepareHeadersAndStream([], $text);
// $headers['Content-Type'] = ['text/html']
// $headers['Content-Length'] = ['14']
```

###  4. Unknown type (object)
```php
class Foo { public function __toString() { return 'foo'; }}
$foo = new Foo();
list($headers, $stream) = $trait->prepareHeadersAndStream([], $foo);
// $headers['Content-Type'] = ['text/plain']
// $stream contains the string 'foo'
```

[Back to Contents](../../index.md)