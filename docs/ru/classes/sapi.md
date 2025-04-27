[Вернуться к оглавлению](../index.md)
# SAPI Explorer

**Explorer** и перечисления **SapiIdentifier** и **SapiMode** — компоненты для определения типа окружения (SAPI) в котором работает приложение.

## Назначение

- Определение типа SAPI (например: CLI, Apache, FastCGI)
- Классификация режима работы приложения: Console, HTTP, Universal
- Упрощение работы с различными средами запуска PHP

## Основные элементы

### Enum SapiIdentifier

| Значение | Описание |
|:---------|:---------|
| CLI | Command Line Interface |
| PHPDBG | PHP Debugger |
| APACHE | Apache Handler |
| CGI | Common Gateway Interface |
| FASTCGI | FastCGI |
| FPM | FastCGI Process Manager |
| LITESPEED | LiteSpeed Server |
| ISAPI | Internet Server API |
| EMBED | Embedded PHP |
| UWSGI | uWSGI Interface |
| UNKNOWN | Неизвестный тип |

**Методы:**

- `getName()` — Получить человекочитаемое название типа.
- `fromString(string $str)` — Создать экземпляр по имени.
- `fromValue(int $value)` — Создать экземпляр по числовому значению.

### Enum SapiMode

| Значение | Описание |
|:---------|:---------|
| CONSOLE | Консольный режим |
| HTTP | HTTP-сервер |
| UNIVERSAL | Универсальный режим (встроенные сервера) |
| UNKNOWN | Неизвестный режим |

**Методы:**

- `getName()` — Получить название режима.
- `fromValue(int $value)` — Получить экземпляр по значению.
- `fromString(string $str)` — Получить экземпляр по строковому описанию.

### Класс Explorer

| Метод | Назначение |
|:------|:-----------|
| `getType()` | Определяет и возвращает текущий `SapiIdentifier` |
| `setType(SapiIdentifier $type)` | Принудительная установка типа окружения |
| `getTypeFamily(?SapiIdentifier $type = null)` | Получает режим работы (`SapiMode`) по типу |

## Примеры использования

### Определение типа и режима

```php
use Scaleum\Stdlib\SAPI\Explorer;

$type = Explorer::getType();
$mode = Explorer::getTypeFamily();

echo $type->getName(); // например: SapiIdentifier::CLI
echo $mode->getName(); // например: SapiMode::CONSOLE, SapiMode::HTTP, SapiMode::UNIVERSAL
```

### Принудительная установка типа

```php
Explorer::setType(SapiIdentifier::CLI);
```

### Получение режима по типу вручную

```php
$mode = Explorer::getTypeFamily(SapiIdentifier::FPM);
```

## Исключения

- `ERuntimeError` выбрасывается при передаче некорректного значения в `fromString()` методов `SapiIdentifier` и `SapiMode`.

[Вернуться к оглавлению](../index.md)