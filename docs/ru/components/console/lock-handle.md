[Назад](./application.md) | [Вернуться к оглавлению](../../index.md)
# LockHandle

`LockHandle` — вспомогательный класс для управления блокировками процессов в CLI-приложениях Scaleum. Сохраняет информацию о файловом дескрипторе, имени процесса и пути к lock-файлу.

## Назначение

- Инкапсулировать ресурс блокировки (file handle) для контроля единственного запуска процесса.
- Хранить имя текущего процесса и путь до lock-файла.
- Проверять корректность переданного дескриптора и выбрасывать исключение при некорректном вводе.

## Свойства

| Свойство           | Тип       | Описание                                                       |
|:-------------------|:----------|:---------------------------------------------------------------|
| `public mixed $fileHandle`  | `resource` | Ресурс файлового дескриптора, используемый для блокировки.     |
| `public string $processName`| `string`   | Идентификатор или имя процесса (например, `sync-job`).         |
| `public string $lockFile`   | `string`   | Абсолютный путь к файлу блокировки (например, `/var/lock/sync.pid`). |

## Конструктор

```php
public function __construct(mixed $fileHandle, string $processName, string $lockFile)
```

1. Проверяет, что `$fileHandle` является валидным ресурсом (`is_resource`).
2. Если проверка не проходит, выбрасывает `ERuntimeError`:
   ```php
   throw new ERuntimeError(
       sprintf('Invalid file handle: %s, given %s', $lockFile, gettype($fileHandle))
   );
   ```
3. Сохраняет параметры в свойства объекта.

## Пример использования

```php
use Scaleum\Console\LockHandle;
use Scaleum\Stdlib\Exceptions\ERuntimeError;

$lockFile = '/var/lock/myjob.lock';
// Открываем или создаём файл для блокировки
$fileHandle = fopen($lockFile, 'c+');
if ($fileHandle === false) {
    throw new ERuntimeError("Cannot open lock file: $lockFile");
}

// Попытка установить эксклюзивную блокировку (функция flock)
if (! flock($fileHandle, LOCK_EX | LOCK_NB)) {
    // Процесс уже запущен
    exit("Another instance is already running.\n");
}

// Инкапсуляция lock handle
$lock = new LockHandle($fileHandle, 'myjob', $lockFile);

// Выполняем задачу...

// После завершения освобождаем блокировку и закрываем файл
flock($lock->fileHandle, LOCK_UN);
fclose($lock->fileHandle);
unlink($lock->lockFile);
```

## Рекомендации

- Используйте `LockHandle` для централизованного хранения и передачи информации о блокировке между компонентами.
- Всегда проверяйте и освобождайте ресурс (`flock` + `fclose`) в блоке `finally` или при завершении процесса.

[Назад](./application.md) | [Вернуться к оглавлению](../../index.md)

