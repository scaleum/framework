[Вернуться к оглавлению](../index.md)
# Session

Компонент `Session` в Scaleum реализует абстрактный механизм работы с сессиями с поддержкой различных драйверов хранения данных.

## Назначение

- Централизованное управление сессиями пользователей
- Поддержка нескольких способов хранения сессий (Redis, Файлы, БД)
- Безопасное управление ID сессии через куки
- Поддержка событийного закрытия и обновления сессии
- Работа с дополнительными метаданными (IP пользователя, User Agent)


## Основные компоненты

| Класс/Интерфейс | Назначение |
|:----------------|:-----------|
| `SessionInterface` | Контракт для работы с сессиями |
| `SessionAbstract` | Базовый абстрактный класс для сессий |
| `RedisSession` | Сессии в Redis |
| `FileSession` | Сессии в файловой системе |
| `DatabaseSession` | Сессии в базе данных |

## Основные возможности

- Работа через универсальный интерфейс
- Поддержка валидации активности сессии (IP, last_activity)
- Хранение данных в Redis / ФС / БД
- Автоматическая очистка устаревших сессий (`cleanup()`)
- Безопасная подпись cookie значений (`salt`, `encode`)
- Поддержка событийного обновления (`KernelEvents::FINISH`)+

## Методы `SessionInterface`

| Метод | Назначение |
|:------|:-----------|
| `get(string\|int $var, mixed $default = false): mixed` | Получить значение из сессии |
| `set(string\|int $var, mixed $value = null, bool $updateImmediately = true): static` | Установить значение |
| `has(string\|int $var): bool` | Проверить наличие переменной |
| `remove(string $key, bool $updateImmediately = true): static` | Удалить переменную |
| `removeByPrefix(string $prefix, bool $updateImmediately = true): static` | Удалить переменные по префиксу |
| `clear(bool $updateImmediately = true): static` | Очистить сессию |
| `getByPrefix(?string $prefix = null): array` | Получить все переменные по префиксу |

## Примеры использования

### Инициализация сессии

```php
$session = new RedisSession([
    'host' => '127.0.0.1',
    'port' => 6379,
    'expiration' => 3600,
]);
// или
$session = new FileSession([
    'path' => '/tmp/sessions/',
]);

// или
$session = new DatabaseSession([
    'database' => $databaseConnection,    
]);
```

### Работа с данными
```php
// Установить данные
$session->set('user_id', 123);

// Получить данные
$userId = $session->get('user_id');

// Удалить данные
$session->remove('user_id');

// Очистить всю сессию
$session->clear();
```

### Проверка активности сессии
```php
if (! $session->isValid()) {
    $session->clear();
    // пользователь должен авторизоваться заново
}
```

## Драйверы сессий
`RedisSession`  
- Хранит каждую переменную как отдельный ключ в Redis.
- Хранит сессии в виде сериализованного текста (`$data = base64_encode(gzcompress(serialize($data)))`).
- Ключи имеют формат: `namespace:session_id:variable`.

`FileSession`  
- Хранит сессии в виде текстовых файлов (`$data = serialize($data)`).
- Автоматически очищает устаревшие файлы при случайном триггере.

`DatabaseSession`  
- Хранит сессии в таблице базы данных.
- Хранит сессии в виде сериализованного текста (`$data = base64_encode(gzcompress(serialize($data)))`).
- При необходимости автоматически создаёт таблицу `sessions`(настраиваемое значение).
- Оптимизирован под массовую очистку устаревших записей.


## Безопасность работы с сессией
- Генерация уникального ID на основе IP + случайного префикса.
- Хеширование значений cookie с помощью md5 + salt.
- Возможность безопасного обновления cookie без перезапуска запроса.
- Защита от подмены IP и User Agent.

## Методы безопасности
Метод | Назначение
|:------|:------|
`isValid()` | Проверяет актуальность сессии
`getAnchor(string $key)` | Получает значение cookie с валидацией подписи
`setAnchor(string $key, mixed $value)` | Устанавливает cookie с защитой подписи

## Пример полного использования
```php
$session = new FileSession([
    'path' => '/tmp/sessions/',
]);

// Записать пользователя
$session->set('user', ['id' => 123, 'name' => 'Maxim']);

// Проверить наличие данных
if ($session->has('user')) {
    $user = $session->get('user');
    echo "Hello, " . $user['name'];
}

// Очистить по префиксу
$session->removeByPrefix('cart_');

// Закрыть и очистить сессию при завершении
$session->close();
```

## Ошибки
Исключение | Условие
|:------|:------|
`ERuntimeError` | Ошибка при отсутствии `EventManager` или `Database`


[Вернуться к оглавлению](../index.md)