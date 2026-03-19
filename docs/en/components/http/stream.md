[Back to Contents](../../index.md)

**EN** | [UK](../../../uk/components/http/stream.md) | [RU](../../../ru/components/http/stream.md)
#  Stream

`Stream` — implementation of PSR-7 `StreamInterface`, a wrapper around a PHP resource for working with streams. Provides reading, writing, positioning, and metadata retrieval.

##  Purpose

- Managing a PHP resource (`resource`) as a stream.
- Supporting read/write operations (`read()`, `write()`), availability checks (`isReadable()`, `isWritable()`).
- Managing stream position (`seek()`, `rewind()`, `tell()`).
- Retrieving size, contents, and metadata of the stream.
- Safe closing and detaching of the resource.

##  Constructor

```php
public function __construct($resource)
```

- `$resource` — a valid PHP resource (e.g., result of `fopen()`).
- Throws `RuntimeException` if the resource is invalid.
- Determines flags `$seekable`, `$readable`, `$writable` based on stream parameters.

##  Methods

| Method                                      | Description                                                                      |
|:-------------------------------------------|:---------------------------------------------------------------------------------|
| `__toString(): string`                     | Returns the entire stream content as a string; calls `rewind()` if necessary.    |
| `close(): void`                            | Closes the stream resource if it is open.                                       |
| `detach()`                                 | Detaches and returns the resource, making the stream unavailable for operations. |
| `getSize(): ?int`                          | Returns the size of the stream (in bytes) or `null` if unknown.                  |
| `tell(): int`                              | Returns the current position of the stream pointer; throws `RuntimeException` on error. |
| `eof(): bool`                              | Checks if the end of the stream is reached or the resource is closed.            |
| `isSeekable(): bool`                       | Checks if the stream supports `seek()` operations.                               |
| `seek(int $offset, int $whence = SEEK_SET): void` | Moves the stream pointer; may throw `RuntimeException`.                   |
| `rewind(): void`                           | Moves the stream pointer to the beginning (`seek(0)`).                           |
| `isWritable(): bool`                       | Checks if the stream is writable.                                                |
| `write(string $string): int`               | Writes a string to the stream, returns the number of bytes written; throws exception. |
| `isReadable(): bool`                       | Checks if the stream is readable.                                                |
| `read(int $length): string`                | Reads up to `$length` bytes from the stream, returns a string; throws exception. |
| `getContents(): string`                    | Reads the remainder of the stream from the current position to the end.          |
| `getMetadata(string $key = null): mixed`   | Returns the entire metadata array or the value for a specific key.               |

##  Usage Examples

###  1. Working with a temporary stream (Memory Stream)
```php
use Scaleum\Http\Stream;

$stream = new Stream(fopen('php://temp', 'r+'));

// Writing a string
$bytes = $stream->write("Hello, Stream!\n"); // returns number of bytes

// Moving to the beginning and reading contents
$stream->rewind();
echo $stream->getContents(); // "Hello, Stream!\n"
```

###  2. Using __toString()
```php
$stream = new Stream(fopen('php://temp', 'r+'));
$stream->write("Line1\nLine2\n");
// __toString automatically calls rewind() before reading
echo (string)$stream;
// Outputs:
// Line1
// Line2
```

###  3. Detaching the resource and passing it to another object
```php
$resource = fopen('/path/to/file.txt', 'rb');
$stream   = new Stream($resource);

// Detach the resource for direct access
$fileResource = $stream->detach();
// Now $stream is unavailable for reading/writing
fclose($fileResource); // close manually
```

###  4. Getting size and metadata
```php
$stream = new Stream(fopen('php://temp', 'r+'));
$stream->write(str_repeat('A', 1024));

// Size in bytes
echo $stream->getSize(); // 1024

// Stream metadata
$meta = $stream->getMetadata();
echo $meta['mode']; // e.g., 'r+'
```

###  5. Working with file streams
```php
$stream = new Stream(fopen('/var/log/app.log', 'r'));

// Read first 100 bytes
$data = $stream->read(100);

while (! $stream->eof()) {
    $data = $stream->read(4096);
    // Process data chunk...
}

$stream->close(); // close the file
```

[Back to Contents](../../index.md)