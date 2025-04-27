[Вернуться к оглавлению](../index.md)
# ArrayHelper

`ArrayHelper` — утилитарный класс для работы с массивами: безопасный доступ, фильтрация, слияние, проверка структуры.

## Назначение

- Безопасное извлечение значений
- Массовая выборка элементов
- Фильтрация по ключам
- Проверка структуры массива
- Интеллектуальное объединение массивов

## Основные методы

| Метод | Назначение |
|:------|:-----------|
| `element($key, array $haystack, $default = false, $expectedType = null): mixed` | Получить значение по ключу |
| `elements(mixed $keys, array $haystack, mixed $default = false, mixed $expectedType = null, bool $keysPreserve = false): array` | Получить несколько элементов по ключам |
| `filter(mixed $keys, array $haystack): array` | Удалить указанные элементы |
| `keyFirst(array $array): mixed\|null` | Получить первый ключ |
| `keyLast(array $array): mixed\|null` | Получить последний ключ |
| `keysExists(array $keys, array $haystack): bool` | Проверить наличие нескольких ключей |
| `search(mixed $needle, array $haystack, bool $strict = false, mixed $column = null)` | Поиск значения |
| `isAssociative(array $array): bool` | Проверить ассоциативность массива |
| `merge(array ...$arrays): array` | Умное слияние массивов |

---

## Примеры использования

### Извлечение одного элемента

```php
$data = ['id' => 123, 'name' => 'Maxim'];

$id = ArrayHelper::element('id', $data); // 123
$age = ArrayHelper::element('age', $data, 18); // 18 (дефолт)
```

### Извлечение нескольких элементов
```php
$data = ['id' => 123, 'name' => 'Maxim', 'role' => 'admin'];

$info = ArrayHelper::elements(['id', 'role'], $data);
// ['id' => 123, 'role' => 'admin']
```

### Фильтрация массива
```php
$data = ['id' => 123, 'name' => 'Maxim', 'role' => 'admin'];

$filtered = ArrayHelper::filter(['role'], $data);
// ['id' => 123, 'name' => 'Maxim']
```

### Получение первого/последнего ключа
```php
$data = ['id' => 123, 'name' => 'Maxim'];

$firstKey = ArrayHelper::keyFirst($data); // 'id'
$lastKey  = ArrayHelper::keyLast($data);  // 'name'
```

### Проверка существования ключей
```php
$data = ['id' => 123, 'name' => 'Maxim'];

$exists = ArrayHelper::keysExists(['id', 'role'], $data); // true (id существует)
```

### Поиск значения в массиве
```php
$data = ['apple', 'banana', 'cherry'];

$found = ArrayHelper::search('banana', $data); // 1
```

### Проверка, является ли массив ассоциативным
```php
$assoc = ['name' => 'Maxim', 'role' => 'admin'];
$indexed = ['apple', 'banana'];

$isAssoc = ArrayHelper::isAssociative($assoc); // true
$isAssoc = ArrayHelper::isAssociative($indexed); // false
```

### Умное слияние массивов
```php
$base = ['id' => 1, 'tags' => ['php']];
$override = ['tags' => ['helpers', 'arrays']];

$result = ArrayHelper::merge($base, $override);
// ['id' => 1, 'tags' => ['php', 'helpers', 'arrays']]
```

## Особенности
- Автоматическая проверка типа извлекаемого значения (`expectedType` через `TypeHelper`)
- Интеллектуальная обработка числовых/строковых ключей при слиянии (`merge()`)
- Безопасная работа с несуществующими элементами массива


[Вернуться к оглавлению](../index.md)