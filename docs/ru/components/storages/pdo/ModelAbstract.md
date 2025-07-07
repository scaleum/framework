[Вернуться к оглавлению](../../index.md)

# ModelAbstract

`ModelAbstract` — базовый **Active‑Record**‑класс в Scaleum для работы с `PDO`‑хранилищем. Он реализует большую часть контракта `ModelInterface`, включая управление состояниями (`insert`, `update`, `readonly`) и **поддержку связей** (*relations*). Все данные экземпляра хранятся во внутреннем объекте `ModelData`, а соединение с БД передаётся через конструктор.

---

## Свойства

| Свойство      | Тип                                                     | Назначение                                           |
| ------------- | ------------------------------------------------------- | ---------------------------------------------------- |
| `$pdo`        | `PDO`                                                   | Активное соединение с базой данных.                  |
| `$data`       | `ModelData`                                             | Контейнер атрибутов модели.                          |
| `$lastStatus` | `array{status:bool,status_text:string,relations:array}` | Результат последней операции.                        |

---

## Конфигурация связей

Каждая модель‑наследник может переопределить метод `getRelations()` и вернуть массив конфигураций:

```php
protected function getRelations(): array
{
    return [
        // Один‑к‑одному (hasOne)
        'profile' => [
            'model'       => ProfileModel::class,   // класс связанной модели
            'method'      => 'findByUserId',        // какой метод вызвать для загрузки
            'primary_key' => 'id',                  // PK таблицы profile
            'foreign_key' => 'user_id',             // FK в profile, указывающий на users
            'type'        => 'hasOne',              // hasOne | hasMany | belongsTo
            'persist'     => true,                  // сохранять автоматически
        ],

        // Один‑ко‑многим (hasMany)
        'posts' => [
            'model'       => PostModel::class,
            'method'      => 'findByUserId',
            'primary_key' => 'id',
            'foreign_key' => 'user_id',
            'type'        => 'hasMany',
            'persist'     => true,
        ],
    ];
}
```

### Жизненный цикл связей

| Фаза           | Что делает `ModelAbstract`|
| -------------- | ------------------------- |
| **Загрузка**   | После `find()` / `findAll*()` вызывает `loadRelations()`, который инициализирует модели из конфигурации и подгружает данные в свойства (`$this->profile`, `$this->posts`). |
| **Сохранение** | В `insert()` / `update()` метод `updateRelations()` определяет новые/изменённые/удалённые связанные объекты и вызывает их `insert()` / `update()` / `delete()`.|
| **Удаление**   | `delete(true)` рекурсивно удаляет все связи, у которых `persist === true`. |

`persist = false` отключает автоматическое сохранение — связь будет только загружаться.

---

## Ключевые методы (public)

| Подпись                                         | Возвращаемый тип | Назначение                                                                        |
| ----------------------------------------------- | ---------------- | --------------------------------------------------------------------------------- |
| `__construct(PDO $pdo, array $attributes = [])` | —                | Принимает соединение и исходные атрибуты; выбирает режим `insert` или `readonly`. |
| `load(array $input): self`                      | `self`           | Загружает данные во внутренний `ModelData`, переводит модель в режим `update`.    |
| `find(mixed $id): ?self`                        | `self\|null`     | Загружает запись с учётом связей.                                                 |
| `insert(): int`                                 | `int`            | Вставляет запись и связанные модели (`persist = true`).                           |
| `update(): int`                                 | `int`            | Обновляет запись и синхронизирует изменения связей.                               |
| `delete(bool $cascade = false): int`            | `int`            | Удаляет запись; при `$cascade = true` рекурсивно удаляет связи.                   |
| `isExisting(): bool`                            | `bool`           | Модель была получена из БД?                                                       |
| `getId(): mixed`                                | `mixed`          | Значение первичного ключа.                                                        |
| `toArray(bool $strict = true): array`           | `array`          | Ассоциативный массив данных; если `$strict = false`, включает связанные объекты.  |

---

## Полный пример:

```php
<?php

declare(strict_types=1);

namespace App\Models;

use Scaleum\Storages\PDO\ModelAbstract;
use PDO;

class UserModel extends ModelAbstract
{
    protected function getTable(): string { return 'users'; }
    protected function getPrimaryKey(): string { return 'id'; }

    protected function getRelations(): array
    {
        return [
            'profile' => [
                'model'       => ProfileModel::class,
                'method'      => 'findByUserId',
                'primary_key' => 'id',
                'foreign_key' => 'user_id',
                'type'        => 'hasOne',
                'persist'     => true,
            ],
            'posts' => [
                'model'       => PostModel::class,
                'method'      => 'findByUserId',
                'primary_key' => 'id',
                'foreign_key' => 'user_id',
                'type'        => 'hasMany',
                'persist'     => true,
            ],
        ];
    }
}

class ProfileModel extends ModelAbstract
{
    protected function getTable(): string { return 'profiles'; }
    protected function getPrimaryKey(): string { return 'id'; }
    protected function getRelations(): array { return []; }
}

class PostModel extends ModelAbstract
{
    protected function getTable(): string { return 'posts'; }
    protected function getPrimaryKey(): string { return 'id'; }
    protected function getRelations(): array { return []; }
}
```

### Сценарий использования

```php
<?php
$pdo = new PDO('sqlite::memory:');

$user = (new UserModel($pdo))->find(1); // подгрузит profile и posts

// Меняем имя и добавляем новый пост
$user->load([
    'user_id' => $user->getId(), // чтобы обновить сущесвующего пользователя
    'name' => 'Bob',    
    'posts' => [ ['title' => 'Новая статья','body'  => 'Текст…'] ] // новый пост
]);


// Всё сохраняется каскадно
$user->update();

// Удаляем пользователя и всё связанное
$user->delete(true);
```

---

## Полезные приёмы

* **Заготовка фабрик**: если модели создаются DI‑контейнером, переопределите `createModelInstance()` или используйте массив `$relationFactories`.
* **Отложенная синхронизация**: установите `persist = false` для больших коллекций, чтобы сохранять их вручную (lazy‑write).
* **Переопределение SQL**: перегрузите `buildInsertSql()` / `buildUpdateSql()` для UPSERT или массовых операций.

[Вернуться к оглавлению](../../index.md)
