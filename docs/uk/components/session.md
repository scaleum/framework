[Вернутися до змісту](../index.md)

[EN](../../en/components/session.md) | **UK** | [RU](../../ru/components/session.md)
# Session

Компонент `Session` у Scaleum реалізує абстрактний механізм роботи з сесіями з підтримкою різних драйверів зберігання даних.

## Призначення

- Централізоване управління сесіями користувачів
- Підтримка кількох способів зберігання сесій (Redis, Файли, БД)
- Безпечне управління ID сесії через куки
- Підтримка подійного закриття та оновлення сесії
- Робота з додатковими метаданими (IP користувача, User Agent)


## Основні компоненти

| Клас/Інтерфейс | Призначення |
|:----------------|:-----------|
| `SessionInterface` | Контракт для роботи з сесіями |
| `SessionAbstract` | Базовий абстрактний клас для сесій |
| `RedisSession` | Сесії в Redis |
| `FileSession` | Сесії у файловій системі |
| `DatabaseSession` | Сесії у базі даних |

## Основні можливості

- Робота через універсальний інтерфейс
- Підтримка валідації активності сесії (IP, last_activity)
- Зберігання даних у Redis / ФС / БД
- Автоматичне очищення застарілих сесій (`cleanup()`)
- Безпечний підпис cookie значень (`salt`, `encode`)
- Підтримка подійного оновлення (`KernelEvents::FINISH`)

## Методи `SessionInterface`

| Метод | Призначення |
|:------|:-----------|
| `get(string\|int $var, mixed $default = false): mixed` | Отримати значення з сесії |
| `set(string\|int $var, mixed $value = null, bool $updateImmediately = true): static` | Встановити значення |
| `has(string\|int $var): bool` | Перевірити наявність змінної |
| `remove(string $key, bool $updateImmediately = true): static` | Видалити змінну |
| `removeByPrefix(string $prefix, bool $updateImmediately = true): static` | Видалити змінні за префіксом |
| `flush(bool $updateImmediately = true): static` | Очистити/скинути сесію |
| `getByPrefix(?string $prefix = null): array` | Отримати всі змінні за префіксом |

## Приклади використання

### Ініціалізація сесії

```php
$session = new RedisSession([
    'host' => '127.0.0.1',
    'port' => 6379,
    'expiration' => 3600,
]);
// або
$session = new FileSession([
    'path' => '/tmp/sessions/',
]);

// або
$session = new DatabaseSession([
    'database' => $databaseConnection,    
]);
```

### Робота з даними
```php
// Встановити дані
$session->set('user_id', 123);

// Отримати дані
$userId = $session->get('user_id');

// Видалити дані
$session->remove('user_id');

// Очистити всю сесію
$session->flush();
```

### Перевірка активності сесії
```php
if (! $session->isValid()) {
    $session->flush();
    // користувач повинен авторизуватися заново
}
```

## Драйвери сесій
`RedisSession`  
- Зберігає кожну змінну як окремий ключ у Redis.
- Зберігає сесії у вигляді серіалізованого тексту (`$data = base64_encode(gzcompress(serialize($data)))`).
- Ключі мають формат: `namespace:session_id:variable`.

`FileSession`  
- Зберігає сесії у вигляді текстових файлів (`$data = serialize($data)`).
- Автоматично очищує застарілі файли при випадковому тригері.

`DatabaseSession`  
- Зберігає сесії у таблиці бази даних.
- Зберігає сесії у вигляді серіалізованого тексту (`$data = base64_encode(gzcompress(serialize($data)))`).
- За потреби автоматично створює таблицю `sessions` (налаштовуване значення).
- Оптимізований під масове очищення застарілих записів.


## Безпека роботи з сесією
- Генерація унікального ID на основі IP + випадкового префікса.
- Хешування значень cookie за допомогою md5 + salt.
- Можливість безпечного оновлення cookie без перезапуску запиту.
- Захист від підміни IP та User Agent.

## Методи безпеки
Метод | Призначення
|:------|:------|
`isValid()` | Перевіряє актуальність сесії
`getAnchor(string $key)` | Отримує значення cookie з валідацією підпису
`setAnchor(string $key, mixed $value)` | Встановлює cookie з захистом підпису

## Приклад повного використання
```php
$session = new FileSession([
    'path' => '/tmp/sessions/',
]);

// Записати користувача
$session->set('user', ['id' => 123, 'name' => 'Maxim']);

// Перевірити наявність даних
if ($session->has('user')) {
    $user = $session->get('user');
    echo "Hello, " . $user['name'];
}

// Очистити за префіксом
$session->removeByPrefix('cart_');

// Закрити і очистити сесію при завершенні
$session->close();
```

## Помилки
Виняток | Умова
|:------|:------|
`ERuntimeError` | Помилка при відсутності `EventManager` або `Database`


[Вернутися до змісту](../index.md)