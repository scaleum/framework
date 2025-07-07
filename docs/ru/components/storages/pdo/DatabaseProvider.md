[Вернуться к оглавлению](../../index.md)

# DatabaseProvider

`DatabaseProvider` — стандартная реализация `DatabaseProviderInterface` из пространства имён `Scaleum\Storages\PDO`. Класс упрощает получение подключения к БД через сервис‑локатор и позволяет переопределять подключение в рантайме.

## Свойства

| Свойство       | Тип              | Доступ    | Значение по умолчанию | Назначение |
| -------------- | ---------------- | --------- | --------------------- | ---------- |
| `$database`    | `Database\|null` | protected | `null`                | Текущее подключение к базе данных. Лениво инициализируется при первом вызове `getDatabase()`.             |
| `$serviceName` | `string`         | protected | `'db'`                | Имя сервиса в `ServiceLocator`, из которого будет взят объект `Database`, если он не установлен напрямую. |

## Методы

| Подпись                                   | Возвращаемый тип | Назначение |
| ----------------------------------------- | ---------------- | ---------- |
| `__construct(?Database $database = null)` | —                | Принимает необязательный экземпляр `Database`. Если передан — сохраняет его во внутреннем свойстве.                                                                                    |
| `getDatabase(): Database`                 | `Database`       | Возвращает объект подключения. Если внутреннее свойство пусто — пытается получить сервис `$serviceName` из `ServiceLocator`; при отсутствии или неверном типе бросает `ERuntimeError`. |
| `setDatabase(Database $database): void`   | `void`           | Подменяет текущее подключение новым экземпляром `Database`.                                                                                                                            |

## Пример использования

```php
<?php

declare(strict_types=1);

use Scaleum\Storages\PDO\DatabaseProvider;
use Scaleum\Storages\PDO\Database;
use Scaleum\Services\ServiceLocator;

// Регистрируем соединение в сервис‑локаторе
ServiceLocator::set('db', new Database('sqlite::memory:'));

$provider = new DatabaseProvider(); // переменная lowerCamelCase

// Получаем подключение (будет взято из ServiceLocator)
$db = $provider->getDatabase();
$db->query('CREATE TABLE users (id INTEGER PRIMARY KEY, name TEXT)');

// Подменяем подключение на другое (например, MySQL в production)
$provider->setDatabase(new Database('mysql:host=db;dbname=prod', 'root', 'secret'));
```

## Практические советы

* **Lazy‑load**: если сервис `$serviceName` ещё не зарегистрирован, создайте подключение вручную и передайте его в конструктор `DatabaseProvider`.
* **Тестирование**: в модульных тестах передавайте мок‑объекты `Database` через `setDatabase()` для изоляции от реального хранилища.
* **Множественные подключения**: при работе с несколькими базами заведите несколько провайдеров с разными `$serviceName` или храните провайдеры в DI‑контейнере.

[Вернуться к оглавлению](../../index.md)
