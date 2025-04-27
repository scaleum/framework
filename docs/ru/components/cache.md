[Вернуться к оглавлению](./index.md)
# Cache
Компонент `Cache` в Scaleum обеспечивает централизованную работу с кэшированием, абстрагируя взаимодействие через интерфейс `CacheInterface`.

## Назначение

- Унифицированный доступ к кэшу
- Возможность динамической подстановки разных драйверов
- Централизация включения/отключения кэширования
- Поддержка нескольких драйверов кэширования

## Основные возможности

- Работа через интерфейс `CacheInterface`
- Гибкая установка драйверов (`RedisDriver`, `FilesystemDriver`, `NullDriver`)
- Управление активностью кэширования (`enabled`)
- Ленивое подключение драйвера (`getDriverDefault()`)

## Интерфейс `CacheInterface`

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

## Реализация `Cache`
Класс Cache реализует шаблон прокси:
- Все операции делегируются текущему драйверу (`CacheInterface`).
- Проверяется включено ли кэширование (`enabled`).
- При необходимости подгружается драйвер по умолчанию (`NullDriver`).

## Поддерживаемые драйверы
Драйвер | Назначение
|:------|:-----------|
`RedisDriver` | Кэширование в Redis
`FilesystemDriver` | Кэширование в файловую систему
`NullDriver` | Заглушка без реального кэширования

## Примеры использования

#### Быстрая настройка драйвера
```php
$cache = new Cache();
$cache->setEnabled(true);
$cache->setDriver(new RedisDriver([
    'host' => '127.0.0.1',
    'port' => 6379,
]));
```
#### Сохранение данных в кэш
```php
$cache->save('user_123', ['id' => 123, 'name' => 'Maxim']);
```

#### Чтение данных из кэша
```php
$user = $cache->get('user_123');
if ($user) {
    echo $user['name']; // Maxim
}
```

#### Проверка наличия ключа
```php
if ($cache->has('user_123')) {
    echo "Кэш найден!";
}
```

#### Удаление элемента из кэша
```php
$cache->delete('user_123');
```

#### Очистка всего кэша
```php
$cache->clean();
```

## Методы класса `Cache`
Метод | Назначение
|:------|:-----------|
`setEnabled(bool $enabled): self` | Включить/выключить использование кэша
`getEnabled(): bool` | Проверить состояние кэширования
`setDriver(mixed $driver): self` | Установить драйвер кэша
`getDriver(): CacheInterface` | Получить текущий драйвер
`clean(): bool` | Очистить весь кэш
`has(string $id): bool` | Проверить наличие элемента по ключу
`get(string $id): mixed` | Получить элемент по ключу
`save(string $id, mixed $data): bool` | Сохранить данные по ключу
`delete(string $id): bool` | Удалить элемент по ключу
`getMetadata(string $id): mixed` | Получить метаданные по ключу

## Особенности
- При отключённом кэшировании (`enabled = false`) все операции возвращают дефолтные значения (false, null).
- Если драйвер не установлен явно, используется `NullDriver`.
- Поддерживается автоматическое создание драйвера через `createInstance()` при установке строки.

## Ошибки
Исключение | Условие
|:------|:-----------|
`InvalidArgumentException` | Передан некорректный драйвер


[Вернуться к оглавлению](./index.md)