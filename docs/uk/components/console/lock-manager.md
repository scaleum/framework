[Назад](./application.md) | [Повернутись до змісту](../../index.md)

[EN](../../../en/components/console/lock-manager.md) | **UK** | [RU](../../../ru/components/console/lock-manager.md)
# LockManager

`LockManager` — клас для керування lock-файлами в консольних додатках Scaleum. Забезпечує механізм запобігання одночасного запуску одноіменних задач, запис і перевірку PID, а також звільнення і очищення застарілих блокувань.

## Призначення

- Створювати і перевіряти lock-файли для унікальних імен процесів.
- Записувати PID поточного процесу в lock-файл при успішній блокуванні.
- Звільняти блокування через видалення lock-файла і закриття ресурсу.
- Видаляти stale-файли, якщо процес завершився.

## Властивості

| Властивість           | Тип         | Опис                                                                                 |
|:-------------------|:------------|:-----------------------------------------------------------------------------------------|
| `protected string $lockDir` | `string`       | Папка для зберігання lock-файлів (за замовчуванням `__DIR__/locks/`).                          |

## Конструктор

```php
public function __construct(string $lockDir = null)
```
- Приймає шлях до директорії для lock-файлів. Якщо `null`, використовується `__DIR__/locks/`.
- Створює каталог з правами `0777`, якщо він не існує.

## Методи

### lock()
```php
public function lock(string $processName): ?LockHandle
```
- Формує шлях `<lockDir>/<processName>.lock`.
- Перевіряє можливість запису в директорію, інакше кидає `ERuntimeError`.
- Відкриває файл у режимі `c+`. При помилці також кидає `ERuntimeError`.
- Намагається встановити неблокуючу виключну блокування (`flock`):
  - Якщо файл вже містить PID активного PHP-процесу, повертає `null`.
  - Інакше очищує файл, записує поточний PID (`getmypid()`), скидає буфер і звільняє блокування.
- Повертає `LockHandle` з ресурсом, ім’ям процесу і шляхом до файлу при успішній блокуванні.

### release()
```php
public function release(LockHandle $handle): void
```
- Приймає раніше отриманий `LockHandle`.
- Перевіряє, що процес все ще заблокований (`isLocked`).
- Якщо ресурс валідний (`is_resource`), закриває дескриптор і видаляє lock-файл.

### isLocked()
```php
public function isLocked(string $processName): bool
```
- Перевіряє наявність файлу `<lockDir>/<processName>.lock`.
- Зчитує PID і повертає `true`, якщо процес з цим PID запущений і є PHP-процесом.

### cleanup()
```php
public function cleanup(): void
```
- Перебирає всі `*.lock` у каталозі `lockDir`.
- Для кожного файлу читає PID. Якщо процес з цим PID не існує, видаляє файл.

### getFilename()
```php
protected function getFilename(string $basename): string
```
- Повертає повний шлях до lock-файла за базовим іменем: `"{$lockDir}$basename.lock"`.

## Приклад використання

```php
use Scaleum\Console\LockManager;

$lockMgr = new LockManager('/tmp/myapp/locks/');

// Спроба блокування процесу "sync"
$lockHandle = $lockMgr->lock('sync');
if (! $lockHandle) {
    exit("Another instance is already running.\n");
}

// Виконання задачі...

// По завершенню: звільнення блокування
$lockMgr->release($lockHandle);

// Очищення всіх застарілих lock-файлів
$lockMgr->cleanup();
```

[Назад](./application.md) | [Повернутись до змісту](../../index.md)