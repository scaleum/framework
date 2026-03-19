[Back](./application.md) | [Return to contents](../../index.md)

**EN** | [UK](../../../uk/components/console/lock-handle.md) | [RU](../../../ru/components/console/lock-handle.md)
#  LockHandle

`LockHandle` is a helper class for managing process locks in Scaleum CLI applications. It stores information about the file descriptor, process name, and path to the lock file.

##  Purpose

- Encapsulate the lock resource (file handle) to control single process execution.
- Store the current process name and the path to the lock file.
- Validate the passed descriptor and throw an exception on invalid input.

##  Properties

| Property           | Type       | Description                                                    |
|:-------------------|:-----------|:---------------------------------------------------------------|
| `public mixed $fileHandle`  | `resource` | File descriptor resource used for locking.                    |
| `public string $processName`| `string`   | Identifier or name of the process (e.g., `sync-job`).          |
| `public string $lockFile`   | `string`   | Absolute path to the lock file (e.g., `/var/lock/sync.pid`).   |

##  Constructor

```php
public function __construct(mixed $fileHandle, string $processName, string $lockFile)
```

1. Checks that `$fileHandle` is a valid resource (`is_resource`).
2. If the check fails, throws `ERuntimeError`:
   ```php
   throw new ERuntimeError(
       sprintf('Invalid file handle: %s, given %s', $lockFile, gettype($fileHandle))
   );
   ```
3. Saves the parameters to the object properties.

##  Usage example

```php
use Scaleum\Console\LockHandle;
use Scaleum\Stdlib\Exceptions\ERuntimeError;

$lockFile = '/var/lock/myjob.lock';
// Open or create the lock file
$fileHandle = fopen($lockFile, 'c+');
if ($fileHandle === false) {
    throw new ERuntimeError("Cannot open lock file: $lockFile");
}

// Attempt to acquire an exclusive lock (flock function)
if (! flock($fileHandle, LOCK_EX | LOCK_NB)) {
    // Process is already running
    exit("Another instance is already running.\n");
}

// Encapsulate the lock handle
$lock = new LockHandle($fileHandle, 'myjob', $lockFile);

// Perform the task...

// After completion, release the lock and close the file
flock($lock->fileHandle, LOCK_UN);
fclose($lock->fileHandle);
unlink($lock->lockFile);
```

##  Recommendations

- Use `LockHandle` for centralized storage and passing of lock information between components.
- Always check and release the resource (`flock` + `fclose`) in a `finally` block or upon process termination.

[Back](./application.md) | [Return to contents](../../index.md)

