[Повернутись до змісту](../index.md)

[EN](../../en/classes/sapi.md) | **UK** | [RU](../../ru/classes/sapi.md)
# SAPI Explorer

**Explorer** та перерахування **SapiIdentifier** і **SapiMode** — компоненти для визначення типу оточення (SAPI), в якому працює застосунок.

## Призначення

- Визначення типу SAPI (наприклад: CLI, Apache, FastCGI)
- Класифікація режиму роботи застосунку: Console, HTTP, Universal
- Спрощення роботи з різними середовищами запуску PHP

## Основні елементи

### Enum SapiIdentifier

| Значення | Опис |
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
| UNKNOWN | Невідомий тип |

**Методи:**

- `getName()` — Отримати людинозрозумілу назву типу.
- `fromString(string $str)` — Створити екземпляр за ім’ям.
- `fromValue(int $value)` — Створити екземпляр за числовим значенням.

### Enum SapiMode

| Значення | Опис |
|:---------|:---------|
| CONSOLE | Консольний режим |
| HTTP | HTTP-сервер |
| UNIVERSAL | Універсальний режим (вбудовані сервери) |
| UNKNOWN | Невідомий режим |

**Методи:**

- `getName()` — Отримати назву режиму.
- `fromValue(int $value)` — Отримати екземпляр за значенням.
- `fromString(string $str)` — Отримати екземпляр за рядковим описом.

### Клас Explorer

| Метод | Призначення |
|:------|:-----------|
| `getType()` | Визначає і повертає поточний `SapiIdentifier` |
| `setType(SapiIdentifier $type)` | Примусове встановлення типу оточення |
| `getTypeFamily(?SapiIdentifier $type = null)` | Отримує режим роботи (`SapiMode`) за типом |

## Приклади використання

### Визначення типу і режиму

```php
use Scaleum\Stdlib\SAPI\Explorer;

$type = Explorer::getType();
$mode = Explorer::getTypeFamily();

echo $type->getName(); // наприклад: SapiIdentifier::CLI
echo $mode->getName(); // наприклад: SapiMode::CONSOLE, SapiMode::HTTP, SapiMode::UNIVERSAL
```

### Примусове встановлення типу

```php
Explorer::setType(SapiIdentifier::CLI);
```

### Отримання режиму за типом вручну

```php
$mode = Explorer::getTypeFamily(SapiIdentifier::FPM);
```

## Винятки

- `ERuntimeError` викидається при передачі некоректного значення в `fromString()` методів `SapiIdentifier` і `SapiMode`.

[Повернутись до змісту](../index.md)