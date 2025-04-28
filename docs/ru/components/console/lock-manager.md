[Назад](./application.md) | [Вернуться к оглавлению](../../index.md)
# LockManager

`LockManager` — класс для управления lock-файлами в консольных приложениях Scaleum. Обеспечивает механизм предотвращения одновременного запуска одноимённых задач, запись и проверку PID, а также освобождение и очистку устаревших блокировок.

## Назначение

- Создавать и проверять lock-файлы для уникальных имён процессов.
- Записывать PID текущего процесса в lock-файл при успешной блокировке.
- Освобождать блокировку через удаление lock-файла и закрытие ресурса.
- Удалять stale-файлы, если процесс завершился.

## Свойства

| Свойство           | Тип         | Описание                                                                                 |
|:-------------------|:------------|:-----------------------------------------------------------------------------------------|
| `protected string $lockDir` | `string`       | Папка для хранения lock-файлов (по умолчанию `__DIR__/locks/`).                          |

## Конструктор

```php
public function __construct(string $lockDir = null)
```
- Принимает путь к директории для lock-файлов. Если `null`, используется `__DIR__/locks/`.
- Создаёт каталог с правами `0777`, если он не существует.

## Методы

### lock()
```php
public function lock(string $processName): ?LockHandle
```
- Формирует путь `<lockDir>/<processName>.lock`.
- Проверяет возможность записи в директорию, иначе кидает `ERuntimeError`.
- Открывает файл в режиме `c+`. При ошибке тоже бросает `ERuntimeError`.
- Пытается установить неблокирующую эксклюзивную блокировку (`flock`):
  - Если файл уже содержит PID активного PHP-процесса, возвращает `null`.
  - Иначе очищает файл, записывает текущий PID (`getmypid()`), сбрасывает буфер и освобождает блокировку.
- Возвращает `LockHandle` с ресурсом, именем процесса и путём к файлу при успешной блокировке.

### release()
```php
public function release(LockHandle $handle): void
```
- Принимает ранее полученный `LockHandle`.
- Проверяет, что процесс всё ещё заблокирован (`isLocked`).
- Если ресурс валиден (`is_resource`), закрывает дескриптор и удаляет lock-файл.

### isLocked()
```php
public function isLocked(string $processName): bool
```
- Проверяет наличие файла `<lockDir>/<processName>.lock`.
- Считывает PID и возвращает `true`, если процесс с этим PID запущен и является PHP-процессом.

### cleanup()
```php
public function cleanup(): void
```
- Перебирает все `*.lock` в каталоге `lockDir`.
- Для каждого файла читает PID. Если процесс с этим PID не существует, удаляет файл.

### getFilename()
```php
protected function getFilename(string $basename): string
```
- Возвращает полный путь до lock-файла по базовому имени: `"{$lockDir}$basename.lock"`.

## Пример использования

```php
use Scaleum\Console\LockManager;

$lockMgr = new LockManager('/tmp/myapp/locks/');

// Попытка блокировки процесса "sync"
$lockHandle = $lockMgr->lock('sync');
if (! $lockHandle) {
    exit("Another instance is already running.\n");
}

// Выполнение задачи...

// По завершении: освобождение блокировки
$lockMgr->release($lockHandle);

// Очистка всех устаревших lock-файлов
$lockMgr->cleanup();
```

[Назад](./application.md) | [Вернуться к оглавлению](../../index.md)
