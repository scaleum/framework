[Вернутися до змісту](../../index.md)

[EN](../../../en/components/http/stream.md) | **UK** | [RU](../../../ru/components/http/stream.md)
# Stream

`Stream` — реалізація PSR-7 `StreamInterface`, обгортка над ресурсом PHP для роботи з потоками. Забезпечує читання, запис, позиціонування та отримання метаданих.

## Призначення

- Управління ресурсом PHP (`resource`) у вигляді потоку.
- Підтримка операцій читання/запису (`read()`, `write()`), перевірки доступності (`isReadable()`, `isWritable()`).
- Управління позицією потоку (`seek()`, `rewind()`, `tell()`).
- Отримання розміру, вмісту та метаданих потоку.
- Безпечне закриття та від’єднання ресурсу.

## Конструктор

```php
public function __construct($resource)
```

- `$resource` — валідний PHP-ресурс (наприклад, результат `fopen()`).
- При невалідному ресурсі кидає `RuntimeException`.
- Визначає прапорці `$seekable`, `$readable`, `$writable` за параметрами потоку.

## Методи

| Метод                                      | Опис                                                                             |
|:-------------------------------------------|:---------------------------------------------------------------------------------|
| `__toString(): string`                     | Повертає весь вміст потоку як рядок; за потреби виконує `rewind()`.               |
| `close(): void`                            | Закриває ресурс потоку, якщо він відкритий.                                      |
| `detach()`                                 | Від’єднує та повертає ресурс, робить потік недоступним для операцій.              |
| `getSize(): ?int`                          | Повертає розмір потоку (байт) або `null`, якщо невідомий.                         |
| `tell(): int`                              | Повертає поточну позицію вказівника потоку; кидає `RuntimeException` при помилці. |
| `eof(): bool`                              | Перевіряє, чи досягнуто кінця потоку або ресурс закритий.                        |
| `isSeekable(): bool`                       | Перевіряє, чи підтримує потік операції `seek()`.                                |
| `seek(int $offset, int $whence = SEEK_SET): void` | Переміщує вказівник потоку; може кинути `RuntimeException`.              |
| `rewind(): void`                           | Переміщує вказівник потоку на початок (`seek(0)`).                               |
| `isWritable(): bool`                       | Перевіряє, чи можна записувати в потік.                                         |
| `write(string $string): int`               | Записує рядок у потік, повертає кількість записаних байт; кидає виключення.      |
| `isReadable(): bool`                       | Перевіряє, чи можна читати з потоку.                                            |
| `read(int $length): string`                | Читає до `$length` байт з потоку, повертає рядок; кидає виключення.              |
| `getContents(): string`                    | Читає залишок потоку від поточної позиції до кінця.                              |
| `getMetadata(string $key = null): mixed`   | Повертає весь масив метаданих або значення за ключем.                            |

## Приклади використання

### 1. Робота з тимчасовим потоком (Memory Stream)
```php
use Scaleum\Http\Stream;

$stream = new Stream(fopen('php://temp', 'r+'));

// Запис рядка
$bytes = $stream->write("Hello, Stream!\n"); // повертає кількість байт

// Переміщення на початок і читання вмісту
$stream->rewind();
echo $stream->getContents(); // "Hello, Stream!\n"
```

### 2. Використання __toString()
```php
$stream = new Stream(fopen('php://temp', 'r+'));
$stream->write("Line1\nLine2\n");
// __toString автоматично виконає rewind() перед читанням
echo (string)$stream;
// Виведе:
// Line1
// Line2
```

### 3. Від’єднання ресурсу та передача іншому об’єкту
```php
$resource = fopen('/path/to/file.txt', 'rb');
$stream   = new Stream($resource);

// Від’єднуємо ресурс для прямого доступу
$fileResource = $stream->detach();
// Тепер $stream недоступний для читання/запису
fclose($fileResource); // закриваємо вручну
```

### 4. Отримання розміру та метаданих
```php
$stream = new Stream(fopen('php://temp', 'r+'));
$stream->write(str_repeat('A', 1024));

// Розмір у байтах
echo $stream->getSize(); // 1024

// Метадані потоку
$meta = $stream->getMetadata();
echo $meta['mode']; // наприклад, 'r+'
```

### 5. Робота з файловими потоками
```php
$stream = new Stream(fopen('/var/log/app.log', 'r'));

// Читаємо перші 100 байт
$data = $stream->read(100);

while (! $stream->eof()) {
    $data = $stream->read(4096);
    // Обробка чанку даних...
}

$stream->close(); // закриваємо файл
```

[Вернутися до змісту](../../index.md)