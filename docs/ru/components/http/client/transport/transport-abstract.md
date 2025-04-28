[Вернуться к оглавлению](../../../../index.md)
# TransportAbstract

`TransportAbstract` — базовый абстрактный класс для реализации транспортных механизмов в HTTP-клиенте Scaleum. Расширяет `Hydrator`, хранит общие настройки (таймаут, количество редиректов) и предоставляет вспомогательные утилиты.

## Назначение

- Управление количеством редиректов при переадресации (`redirectsCount`).
- Управление таймаутом соединения и получения ответа (`timeout`).
- Предоставление утилитного метода `flatten()` для преобразования заголовков в плоский массив строк.
- Внедрение свойств через `Hydrator` для гибкой конфигурации в подклассах.

## Свойства

| Свойство             | Тип     | Описание                                                 |
|:---------------------|:--------|:---------------------------------------------------------|
| `protected int $redirectsCount` | `int`   | Максимальное число последовательных редиректов (по умолчанию 5). |
| `protected int $timeout`        | `int`   | Таймаут в секундах для установки соединения и ответа (по умолчанию 5). |

## Методы

### static flatten
```php
protected static function flatten(array $array): array
```
Преобразует ассоциативный массив `['Name' => 'value']` или `['Name' => ['v1','v2']]` в плоский массив строк:
```
[ 'Name: value', 'Name: v1', 'Name: v2', ... ]
```

### getRedirectsCount / setRedirectsCount
```php
public function getRedirectsCount(): int;
public function setRedirectsCount(int $redirectsCount): static;
```
- `getRedirectsCount()` — возвращает текущее значение `redirectsCount`.
- `setRedirectsCount($n)` — устанавливает допустимое число редиректов и возвращает `$this`.

### getTimeout / setTimeout
```php
public function getTimeout(): int;
public function setTimeout(int $timeout): static;
```
- `getTimeout()` — возвращает текущее значение `timeout`.
- `setTimeout($seconds)` — устанавливает таймаут в секундах и возвращает `$this`.

## Примеры использования

### 1. Настройка редиректов и таймаута в подклассе
```php
$transport = new CurlTransport()
    ->setRedirectsCount(3)  // разрешить не более 3 редиректов
    ->setTimeout(10);       // таймаут 10 секунд
```

### 2. Использование flatten() при необходимости ручного формирования заголовков
```php
$headersArray = [
    'Accept' => ['text/html', 'application/json'],
    'X-Custom' => 'Value'
];
$flat = TransportAbstract::flatten($headersArray);
// ['Accept: text/html', 'Accept: application/json', 'X-Custom: Value']
```

### 3. Гибкая конфигурация через Hydrator
```php
// Например, в тестах можно подставить параметры через hydrate()
$transport = new CurlTransport();
$transport->hydrate([
    'timeout' => 2,
    'redirectsCount' => 0
]);
// Теперь соединение будет иметь таймаут 2s и не будет следовать редиректам
```

[Вернуться к оглавлению](../../../../index.md)