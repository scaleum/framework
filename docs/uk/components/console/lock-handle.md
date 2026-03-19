[Назад](./application.md) | [Повернутись до змісту](../../index.md)

[EN](../../../en/components/console/lock-handle.md) | **UK** | [RU](../../../ru/components/console/lock-handle.md)
# LockHandle

`LockHandle` — допоміжний клас для керування блокуваннями процесів у CLI-додатках Scaleum. Зберігає інформацію про файловий дескриптор, ім'я процесу та шлях до lock-файлу.

## Призначення

- Інкапсулювати ресурс блокування (file handle) для контролю єдиного запуску процесу.
- Зберігати ім'я поточного процесу та шлях до lock-файлу.
- Перевіряти коректність переданого дескриптора та викидати виключення при некоректному вводі.

## Властивості

| Властивість           | Тип       | Опис                                                           |
|:---------------------|:----------|:---------------------------------------------------------------|
| `public mixed $fileHandle`  | `resource` | Ресурс файлового дескриптора, що використовується для блокування. |
| `public string $processName`| `string`   | Ідентифікатор або ім'я процесу (наприклад, `sync-job`).         |
| `public string $lockFile`   | `string`   | Абсолютний шлях до файлу блокування (наприклад, `/var/lock/sync.pid`). |

## Конструктор

```php
public function __construct(mixed $fileHandle, string $processName, string $lockFile)
```

1. Перевіряє, що `$fileHandle` є валідним ресурсом (`is_resource`).
2. Якщо перевірка не проходить, викидає `ERuntimeError`:
   ```php
   throw new ERuntimeError(
       sprintf('Invalid file handle: %s, given %s', $lockFile, gettype($fileHandle))
   );
   ```
3. Зберігає параметри у властивості об'єкта.

## Приклад використання

```php
use Scaleum\Console\LockHandle;
use Scaleum\Stdlib\Exceptions\ERuntimeError;

$lockFile = '/var/lock/myjob.lock';
// Відкриваємо або створюємо файл для блокування
$fileHandle = fopen($lockFile, 'c+');
if ($fileHandle === false) {
    throw new ERuntimeError("Cannot open lock file: $lockFile");
}

// Спроба встановити ексклюзивне блокування (функція flock)
if (! flock($fileHandle, LOCK_EX | LOCK_NB)) {
    // Процес вже запущено
    exit("Another instance is already running.\n");
}

// Інкапсуляція lock handle
$lock = new LockHandle($fileHandle, 'myjob', $lockFile);

// Виконуємо завдання...

// Після завершення звільняємо блокування та закриваємо файл
flock($lock->fileHandle, LOCK_UN);
fclose($lock->fileHandle);
unlink($lock->lockFile);
```

## Рекомендації

- Використовуйте `LockHandle` для централізованого зберігання та передачі інформації про блокування між компонентами.
- Завжди перевіряйте та звільняйте ресурс (`flock` + `fclose`) у блоці `finally` або при завершенні процесу.

[Назад](./application.md) | [Повернутись до змісту](../../index.md)