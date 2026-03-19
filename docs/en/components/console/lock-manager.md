[Back](./application.md) | [Return to contents](../../index.md)

**EN** | [UK](../../../uk/components/console/lock-manager.md) | [RU](../../../ru/components/console/lock-manager.md)
#  LockManager

`LockManager` is a class for managing lock files in Scaleum console applications. It provides a mechanism to prevent simultaneous execution of identically named tasks, records and checks PID, as well as releases and cleans up stale locks.

##  Purpose

- Create and check lock files for unique process names.
- Write the current process PID to the lock file upon successful locking.
- Release the lock by deleting the lock file and closing the resource.
- Remove stale files if the process has terminated.

##  Properties

| Property           | Type         | Description                                                                              |
|:-------------------|:-------------|:-----------------------------------------------------------------------------------------|
| `protected string $lockDir` | `string`       | Directory for storing lock files (default is `__DIR__/locks/`).                          |

##  Constructor

```php
public function __construct(string $lockDir = null)
```
- Accepts a path to the directory for lock files. If `null`, uses `__DIR__/locks/`.
- Creates the directory with permissions `0777` if it does not exist.

##  Methods

###  lock()
```php
public function lock(string $processName): ?LockHandle
```
- Constructs the path `<lockDir>/<processName>.lock`.
- Checks write permission to the directory, otherwise throws `ERuntimeError`.
- Opens the file in `c+` mode. Throws `ERuntimeError` on failure.
- Attempts to acquire a non-blocking exclusive lock (`flock`):
  - If the file already contains the PID of an active PHP process, returns `null`.
  - Otherwise, clears the file, writes the current PID (`getmypid()`), flushes the buffer, and releases the lock.
- Returns a `LockHandle` with the resource, process name, and file path upon successful locking.

###  release()
```php
public function release(LockHandle $handle): void
```
- Accepts a previously obtained `LockHandle`.
- Checks that the process is still locked (`isLocked`).
- If the resource is valid (`is_resource`), closes the descriptor and deletes the lock file.

###  isLocked()
```php
public function isLocked(string $processName): bool
```
- Checks for the existence of the file `<lockDir>/<processName>.lock`.
- Reads the PID and returns `true` if a process with this PID is running and is a PHP process.

###  cleanup()
```php
public function cleanup(): void
```
- Iterates over all `*.lock` files in the `lockDir` directory.
- For each file, reads the PID. If the process with this PID does not exist, deletes the file.

###  getFilename()
```php
protected function getFilename(string $basename): string
```
- Returns the full path to the lock file by base name: `"{$lockDir}$basename.lock"`.

##  Usage example

```php
use Scaleum\Console\LockManager;

$lockMgr = new LockManager('/tmp/myapp/locks/');

// Attempt to lock the "sync" process
$lockHandle = $lockMgr->lock('sync');
if (! $lockHandle) {
    exit("Another instance is already running.\n");
}

// Perform the task...

// Upon completion: release the lock
$lockMgr->release($lockHandle);

// Clean up all stale lock files
$lockMgr->cleanup();
```

[Back](./application.md) | [Return to contents](../../index.md)
