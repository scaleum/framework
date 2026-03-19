[Повернутись до змісту](./index.md)

[EN](../../en/components/cache.md) | **UK** | [RU](../../ru/components/cache.md)
# Cache
Компонент `Cache` у Scaleum забезпечує централізовану роботу з кешуванням, абстрагуючи взаємодію через інтерфейс `CacheInterface`.

## Призначення

- Уніфікований доступ до кешу
- Можливість динамічної підстановки різних драйверів
- Централізація увімкнення/вимкнення кешування
- Підтримка кількох драйверів кешування

## Основні можливості

- Робота через інтерфейс `CacheInterface`
- Гнучке встановлення драйверів (`RedisDriver`, `FilesystemDriver`, `NullDriver`)
- Керування активністю кешування (`enabled`)
- Ліниве підключення драйвера (`getDriverDefault()`)

## Інтерфейс `CacheInterface`

```php
interface CacheInterface
{
    public function clean(): bool;
    public function has(string $id): bool;
    public function delete(string $id): bool;
    public function get(string $id): mixed;
    public function getMetadata(string $id): mixed;
    public function save(string $id, mixed $data): bool;
}
```

## Реалізація `Cache`
Клас Cache реалізує шаблон проксі:
- Всі операції делегуються поточному драйверу (`CacheInterface`).
- Перевіряється, чи увімкнено кешування (`enabled`).
- За потреби підвантажується драйвер за замовчуванням (`NullDriver`).

## Підтримувані драйвери
Драйвер | Призначення
|:------|:-----------|
`RedisDriver` | Кешування в Redis
`FilesystemDriver` | Кешування у файлову систему
`NullDriver` | Заглушка без реального кешування

## Приклади використання

#### Швидке налаштування драйвера
```php
$cache = new Cache();
$cache->setEnabled(true);
$cache->setDriver(new RedisDriver([
    'host' => '127.0.0.1',
    'port' => 6379,
]));
```
#### Збереження даних у кеш
```php
$cache->save('user_123', ['id' => 123, 'name' => 'Maxim']);
```

#### Читання даних з кешу
```php
$user = $cache->get('user_123');
if ($user) {
    echo $user['name']; // Maxim
}
```

#### Перевірка наявності ключа
```php
if ($cache->has('user_123')) {
    echo "Кеш знайдено!";
}
```

#### Видалення елемента з кешу
```php
$cache->delete('user_123');
```

#### Очищення всього кешу
```php
$cache->clean();
```

## Методи класу `Cache`
Метод | Призначення
|:------|:-----------|
`setEnabled(bool $enabled): self` | Увімкнути/вимкнути використання кешу
`getEnabled(): bool` | Перевірити стан кешування
`setDriver(mixed $driver): self` | Встановити драйвер кешу
`getDriver(): CacheInterface` | Отримати поточний драйвер
`clean(): bool` | Очистити весь кеш
`has(string $id): bool` | Перевірити наявність елемента за ключем
`get(string $id): mixed` | Отримати елемент за ключем
`save(string $id, mixed $data): bool` | Зберегти дані за ключем
`delete(string $id): bool` | Видалити елемент за ключем
`getMetadata(string $id): mixed` | Отримати метадані за ключем

## Особливості
- При вимкненому кешуванні (`enabled = false`) всі операції повертають дефолтні значення (false, null).
- Якщо драйвер не встановлено явно, використовується `NullDriver`.
- Підтримується автоматичне створення драйвера через `createInstance()` при встановленні рядка.

## Помилки
Виняток | Умова
|:------|:-----------|
`InvalidArgumentException` | Передано некоректний драйвер


[Повернутись до змісту](./index.md)