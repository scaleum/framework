[Повернутись до змісту](../index.md)

[EN](../../en/helpers/process-helper.md) | **UK** | [RU](../../ru/helpers/process-helper.md)
# ProcessHelper

`ProcessHelper` — це утилітарний клас для роботи з системними процесами.

## Призначення

- Запуск команд у фоні
- Отримання списку запущених процесів
- Перевірка PID
- Визначення PHP-процесу
- Визначення типу ОС

## Основні методи

| Метод | Призначення |
|:------|:-----------|
| `execute(string $command)` | Запуск команди у фоні |
| `getInterpreter()` | Отримання шляху до PHP-інтерпретатора |
| `getStarted()` | Список PID запущених процесів |
| `isStarted(int $pid)` | Перевірка, чи запущено PID |
| `isPhpProcess(int $pid)` | Перевірка, чи є PID PHP-процесом |
| `isUnixOS()` | Чи є ОС UNIX-системою |
| `isWinOS()` | Чи є ОС Windows |

## Приклади використання

### Запуск команди у фоні

```php
ProcessHelper::execute('php worker.php');
```

### Отримання інтерпретатора PHP

```php
$php = ProcessHelper::getInterpreter();
```

### Список запущених процесів

```php
$pids = ProcessHelper::getStarted();
```

### Перевірка, чи запущено процес

```php
$isRunning = ProcessHelper::isStarted(1234);
```

### Перевірка, чи є PID PHP-процесом

```php
$isPhp = ProcessHelper::isPhpProcess(1234);
```

### Перевірка типу операційної системи

```php
if (ProcessHelper::isUnixOS()) {
    // UNIX/Linux system
}

if (ProcessHelper::isWinOS()) {
    // Windows system
}
```

[Повернутись до змісту](../index.md)