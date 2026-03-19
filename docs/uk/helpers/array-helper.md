[Повернутись до змісту](../index.md)

[EN](../../en/helpers/array-helper.md) | **UK** | [RU](../../ru/helpers/array-helper.md)
# ArrayHelper

`ArrayHelper` — утилітарний клас для роботи з масивами: безпечний доступ, фільтрація, злиття, перевірка структури.

## Призначення

- Безпечне вилучення значень
- Масове вибирання елементів
- Фільтрація за ключами
- Перевірка структури масиву
- Інтелектуальне об’єднання масивів

## Основні методи

| Метод | Призначення |
|:------|:-----------|
| `element($key, array $haystack, $default = false, $expectedType = null): mixed` | Отримати значення за ключем |
| `elements(mixed $keys, array $haystack, mixed $default = false, mixed $expectedType = null, bool $keysPreserve = false): array` | Отримати кілька елементів за ключами |
| `filter(mixed $keys, array $haystack): array` | Видалити вказані елементи |
| `keyFirst(array $array): mixed\|null` | Отримати перший ключ |
| `keyLast(array $array): mixed\|null` | Отримати останній ключ |
| `keysExists(array $keys, array $haystack): bool` | Перевірити наявність кількох ключів |
| `search(mixed $needle, array $haystack, bool $strict = false, mixed $column = null)` | Пошук значення |
| `isAssociative(array $array): bool` | Перевірити асоціативність масиву |
| `merge(array ...$arrays): array` | Розумне злиття масивів |

---

## Приклади використання

### Вилучення одного елемента

```php
$data = ['id' => 123, 'name' => 'Maxim'];

$id = ArrayHelper::element('id', $data); // 123
$age = ArrayHelper::element('age', $data, 18); // 18 (дефолт)
```

### Вилучення кількох елементів
```php
$data = ['id' => 123, 'name' => 'Maxim', 'role' => 'admin'];

$info = ArrayHelper::elements(['id', 'role'], $data);
// ['id' => 123, 'role' => 'admin']
```

### Фільтрація масиву
```php
$data = ['id' => 123, 'name' => 'Maxim', 'role' => 'admin'];

$filtered = ArrayHelper::filter(['role'], $data);
// ['id' => 123, 'name' => 'Maxim']
```

### Отримання першого/останнього ключа
```php
$data = ['id' => 123, 'name' => 'Maxim'];

$firstKey = ArrayHelper::keyFirst($data); // 'id'
$lastKey  = ArrayHelper::keyLast($data);  // 'name'
```

### Перевірка існування ключів
```php
$data = ['id' => 123, 'name' => 'Maxim'];

$exists = ArrayHelper::keysExists(['id', 'role'], $data); // true (id існує)
```

### Пошук значення в масиві
```php
$data = ['apple', 'banana', 'cherry'];

$found = ArrayHelper::search('banana', $data); // 1
```

### Перевірка, чи є масив асоціативним
```php
$assoc = ['name' => 'Maxim', 'role' => 'admin'];
$indexed = ['apple', 'banana'];

$isAssoc = ArrayHelper::isAssociative($assoc); // true
$isAssoc = ArrayHelper::isAssociative($indexed); // false
```

### Розумне злиття масивів
```php
$base = ['id' => 1, 'tags' => ['php']];
$override = ['tags' => ['helpers', 'arrays']];

$result = ArrayHelper::merge($base, $override);
// ['id' => 1, 'tags' => ['php', 'helpers', 'arrays']]
```

## Особливості
- Автоматична перевірка типу вилученого значення (`expectedType` через `TypeHelper`)
- Інтелектуальна обробка числових/рядкових ключів при злитті (`merge()`)
- Безпечна робота з неіснуючими елементами масиву


[Повернутись до змісту](../index.md)