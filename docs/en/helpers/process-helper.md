[Back to Contents](../index.md)

**EN** | [UK](../../uk/helpers/process-helper.md) | [RU](../../ru/helpers/process-helper.md)
#  ProcessHelper

`ProcessHelper` is a utility class for working with system processes.

##  Purpose

- Running commands in the background
- Getting a list of running processes
- Checking PID
- Determining a PHP process
- Determining the OS type

##  Main methods

| Method | Purpose |
|:------|:--------|
| `execute(string $command)` | Run a command in the background |
| `getInterpreter()` | Get the path to the PHP interpreter |
| `getStarted()` | List of PIDs of running processes |
| `isStarted(int $pid)` | Check if a PID is running |
| `isPhpProcess(int $pid)` | Check if a PID is a PHP process |
| `isUnixOS()` | Check if the OS is a UNIX system |
| `isWinOS()` | Check if the OS is Windows |

##  Usage examples

###  Running a command in the background

```php
ProcessHelper::execute('php worker.php');
```

###  Getting the PHP interpreter

```php
$php = ProcessHelper::getInterpreter();
```

###  List of running processes

```php
$pids = ProcessHelper::getStarted();
```

###  Checking if a process is running

```php
$isRunning = ProcessHelper::isStarted(1234);
```

###  Checking if a PID is a PHP process

```php
$isPhp = ProcessHelper::isPhpProcess(1234);
```

###  Checking the operating system type

```php
if (ProcessHelper::isUnixOS()) {
    // UNIX/Linux system
}

if (ProcessHelper::isWinOS()) {
    // Windows system
}
```

[Back to Contents](../index.md)