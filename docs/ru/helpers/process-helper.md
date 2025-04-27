[Вернуться к оглавлению](../index.md)
# ProcessHelper

`ProcessHelper` — это утилитарный класс для работы с системными процессами.

## Назначение

- Запуск команд в фоне
- Получение списка запущенных процессов
- Проверка PID
- Определение PHP-процесса
- Определение типа ос

## Основные методы

| Метод | Назначение |
|:------|:-----------|
| `execute(string $command)` | Запуск команды в фоне |
| `getInterpreter()` | Получение пути к PHP-интерпретатору |
| `getStarted()` | Список PID запущенных процессов |
| `isStarted(int $pid)` | Проверка, запущен ли PID |
| `isPhpProcess(int $pid)` | Проверка, является ли PID PHP-процессом |
| `isUnixOS()` | Является ли ос UNIX-системой |
| `isWinOS()` | Является ли ос Windows |

## Примеры использования

### Запуск команды в фоне

```php
ProcessHelper::execute('php worker.php');
```

### Получение интерпретатора PHP

```php
$php = ProcessHelper::getInterpreter();
```

### Список запущенных процессов

```php
$pids = ProcessHelper::getStarted();
```

### Проверка, запущен ли процесс

```php
$isRunning = ProcessHelper::isStarted(1234);
```

### Проверка, является ли PID PHP-процессом

```php
$isPhp = ProcessHelper::isPhpProcess(1234);
```

### Проверка типа операционной системы

```php
if (ProcessHelper::isUnixOS()) {
    // UNIX/Linux system
}

if (ProcessHelper::isWinOS()) {
    // Windows system
}
```

[Вернуться к оглавлению](../index.md)