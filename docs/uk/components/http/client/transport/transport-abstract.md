[Повернутись до змісту](../../../../index.md)

[EN](../../../../../en/components/http/client/transport/transport-abstract.md) | **UK** | [RU](../../../../../ru/components/http/client/transport/transport-abstract.md)
# TransportAbstract

`TransportAbstract` — базовий абстрактний клас для реалізації транспортних механізмів у HTTP-клієнті Scaleum. Розширює `Hydrator`, зберігає загальні налаштування (таймаут, кількість редиректів) і надає допоміжні утиліти.

## Призначення

- Управління кількістю редиректів при переадресації (`redirectsCount`).
- Управління таймаутом з'єднання та отримання відповіді (`timeout`).
- Надання утилітного методу `flatten()` для перетворення заголовків у плоский масив рядків.
- Впровадження властивостей через `Hydrator` для гнучкої конфігурації у підкласах.

## Властивості

| Властивість             | Тип     | Опис                                                    |
|:------------------------|:--------|:--------------------------------------------------------|
| `protected int $redirectsCount` | `int`   | Максимальна кількість послідовних редиректів (за замовчуванням 5). |
| `protected int $timeout`          | `int`   | Таймаут у секундах для встановлення з'єднання та відповіді (за замовчуванням 5). |

## Методи

### static flatten
```php
protected static function flatten(array $array): array
```
Перетворює асоціативний масив `['Name' => 'value']` або `['Name' => ['v1','v2']]` у плоский масив рядків:
```
[ 'Name: value', 'Name: v1', 'Name: v2', ... ]
```

### getRedirectsCount / setRedirectsCount
```php
public function getRedirectsCount(): int;
public function setRedirectsCount(int $redirectsCount): static;
```
- `getRedirectsCount()` — повертає поточне значення `redirectsCount`.
- `setRedirectsCount($n)` — встановлює допустиму кількість редиректів і повертає `$this`.

### getTimeout / setTimeout
```php
public function getTimeout(): int;
public function setTimeout(int $timeout): static;
```
- `getTimeout()` — повертає поточне значення `timeout`.
- `setTimeout($seconds)` — встановлює таймаут у секундах і повертає `$this`.

## Приклади використання

### 1. Налаштування редиректів і таймауту у підкласі
```php
$transport = new CurlTransport()
    ->setRedirectsCount(3)  // дозволити не більше 3 редиректів
    ->setTimeout(10);       // таймаут 10 секунд
```

### 2. Використання flatten() при необхідності ручного формування заголовків
```php
$headersArray = [
    'Accept' => ['text/html', 'application/json'],
    'X-Custom' => 'Value'
];
$flat = TransportAbstract::flatten($headersArray);
// ['Accept: text/html', 'Accept: application/json', 'X-Custom: Value']
```

### 3. Гнучка конфігурація через Hydrator
```php
// Наприклад, у тестах можна підставити параметри через hydrate()
$transport = new CurlTransport();
$transport->hydrate([
    'timeout' => 2,
    'redirectsCount' => 0
]);
// Тепер з'єднання матиме таймаут 2s і не буде слідувати редиректам
```

[Повернутись до змісту](../../../../index.md)