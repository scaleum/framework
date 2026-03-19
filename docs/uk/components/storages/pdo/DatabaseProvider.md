[Повернутись до змісту](../../index.md)

[EN](../../../../en/components/storages/pdo/DatabaseProvider.md) | **UK** | [RU](../../../../ru/components/storages/pdo/DatabaseProvider.md)
# DatabaseProvider

`DatabaseProvider` — стандартна реалізація `DatabaseProviderInterface` з простору імен `Scaleum\Storages\PDO`. Клас спрощує отримання підключення до БД через сервіс‑локатор і дозволяє перевизначати підключення в рантаймі.

## Властивості

| Властивість    | Тип              | Доступ    | Значення за замовчуванням | Призначення |
| -------------- | ---------------- | --------- | ------------------------- | ----------- |
| `$database`    | `Database\|null` | protected | `null`                    | Поточне підключення до бази даних. Лениво ініціалізується при першому виклику `getDatabase()`.             |
| `$serviceName` | `string`         | protected | `'db'`                    | Ім'я сервісу в `ServiceLocator`, з якого буде взято об'єкт `Database`, якщо він не встановлений напряму. |

## Методи

| Підпис                                   | Повертаємий тип | Призначення |
| ---------------------------------------- | --------------- | ----------- |
| `__construct(?Database $database = null)` | —               | Приймає необов’язковий екземпляр `Database`. Якщо переданий — зберігає його у внутрішньому властивості.                                                                                    |
| `getDatabase(): Database`                 | `Database`      | Повертає об'єкт підключення. Якщо внутрішнє властивість порожнє — намагається отримати сервіс `$serviceName` з `ServiceLocator`; при відсутності або неправильному типі кидає `ERuntimeError`. |
| `setDatabase(Database $database): void`   | `void`          | Замінює поточне підключення новим екземпляром `Database`.                                                                                                                            |

## Приклад використання

```php
<?php

declare(strict_types=1);

use Scaleum\Storages\PDO\DatabaseProvider;
use Scaleum\Storages\PDO\Database;
use Scaleum\Services\ServiceLocator;

// Реєструємо з'єднання в сервіс‑локаторі
ServiceLocator::set('db', new Database('sqlite::memory:'));

$provider = new DatabaseProvider(); // змінна lowerCamelCase

// Отримуємо підключення (буде взято з ServiceLocator)
$db = $provider->getDatabase();
$db->query('CREATE TABLE users (id INTEGER PRIMARY KEY, name TEXT)');

// Замінюємо підключення на інше (наприклад, MySQL у production)
$provider->setDatabase(new Database('mysql:host=db;dbname=prod', 'root', 'secret'));
```

## Практичні поради

* **Lazy‑load**: якщо сервіс `$serviceName` ще не зареєстрований, створіть підключення вручну і передайте його в конструктор `DatabaseProvider`.
* **Тестування**: у модульних тестах передавайте мок‑об’єкти `Database` через `setDatabase()` для ізоляції від реального сховища.
* **Багато підключень**: при роботі з кількома базами заведiть кілька провайдерів з різними `$serviceName` або зберігайте провайдери в DI‑контейнері.

[Повернутись до змісту](../../index.md)