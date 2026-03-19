[Вернутися до змісту](../../index.md)

[EN](../../../../en/components/storages/pdo/ModelAbstract.md) | **UK** | [RU](../../../../ru/components/storages/pdo/ModelAbstract.md)
#  ModelAbstract

`ModelAbstract` — базовий **Active‑Record**‑клас у Scaleum для роботи з `PDO`‑сховищем. Він реалізує більшу частину контракту `ModelInterface`, включно з управлінням станами (`insert`, `update`, `readonly`) та **підтримкою зв’язків** (*relations*). Всі дані екземпляра зберігаються у внутрішньому об’єкті `ModelData`, а з’єднання з БД передається через конструктор.

---

##  Властивості

| Властивість   | Тип                                                      | Призначення                                         |
| ------------- | -------------------------------------------------------- | -------------------------------------------------- |
| `$pdo`        | `PDO`                                                    | Активне з’єднання з базою даних.                    |
| `$data`       | `ModelData`                                              | Контейнер атрибутів моделі.                         |
| `$lastStatus` | `array{status:bool,status_text:string,relations:array}` | Результат останньої операції.                        |

---

##  Конфігурація зв’язків

Кожна модель-нащадок може перевизначити метод `getRelations()` і повернути масив конфігурацій:

```php
protected function getRelations(): array
{
    return [
        // Один‑до‑одного (hasOne)
        'profile' => [
            'model'       => ProfileModel::class,   // клас пов’язаної моделі
            'method'      => 'findByUserId',        // який метод викликати для завантаження
            'primary_key' => 'id',                  // PK таблиці profile
            'foreign_key' => 'user_id',             // FK у profile, що вказує на users
            'type'        => 'hasOne',              // hasOne | hasMany | belongsTo
            'persist'     => true,                  // зберігати автоматично
        ],

        // Один‑до‑багатьох (hasMany)
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

###  Життєвий цикл зв’язків

| Фаза           | Що робить `ModelAbstract`                                                                                   |
| -------------- | ----------------------------------------------------------------------------------------------------------- |
| **Завантаження** | Після `find()` / `findAll*()` викликає `loadRelations()`, який ініціалізує моделі з конфігурації та підвантажує дані у властивості (`$this->profile`, `$this->posts`). |
| **Збереження**  | У `insert()` / `update()` метод `updateRelations()` визначає нові/змінені/видалені пов’язані об’єкти і викликає їх `insert()` / `update()` / `delete()`. |
| **Видалення**   | `delete(true)` рекурсивно видаляє всі зв’язки, у яких `persist === true`.                                   |

`persist = false` вимикає автоматичне збереження — зв’язок буде лише завантажуватися.

---

##  Ключові методи (public)

| Підпис                                          | Повертаємий тип | Призначення                                                                       |
| ----------------------------------------------- | --------------- | -------------------------------------------------------------------------------- |
| `__construct(PDO $pdo, array $attributes = [])` | —               | Приймає з’єднання та початкові атрибути; обирає режим `insert` або `readonly`.    |
| `load(array $input): self`                      | `self`          | Завантажує дані у внутрішній `ModelData`, переводить модель у режим `update`.     |
| `find(mixed $id): ?self`                        | `self\|null`    | Завантажує запис з урахуванням зв’язків.                                         |
| `insert(): int`                                 | `int`           | Вставляє запис і пов’язані моделі (`persist = true`).                            |
| `update(): int`                                 | `int`           | Оновлює запис і синхронізує зміни зв’язків.                                      |
| `delete(bool $cascade = false): int`            | `int`           | Видаляє запис; при `$cascade = true` рекурсивно видаляє зв’язки.                 |
| `isExisting(): bool`                            | `bool`          | Модель була отримана з БД?                                                       |
| `getId(): mixed`                                | `mixed`         | Значення первинного ключа.                                                       |
| `toArray(bool $strict = true): array`           | `array`         | Асоціативний масив даних; якщо `$strict = false`, включає пов’язані об’єкти.     |

---

##  Повний приклад:

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

###  Сценарій використання

```php
<?php
$pdo = new PDO('sqlite::memory:');

$user = (new UserModel($pdo))->find(1); // підвантажить profile і posts

// Змінюємо ім'я та додаємо новий пост
$user->load([
    'user_id' => $user->getId(), // щоб оновити існуючого користувача
    'name' => 'Bob',    
    'posts' => [ ['title' => 'Нова стаття','body'  => 'Текст…'] ] // новий пост
]);


// Все зберігається каскадно
$user->update();

// Видаляємо користувача і все пов’язане
$user->delete(true);
```

---

##  Корисні прийоми

* **Заготовка фабрик**: якщо моделі створюються DI‑контейнером, перевизначте `createModelInstance()` або використовуйте масив `$relationFactories`.
* **Відкладена синхронізація**: встановіть `persist = false` для великих колекцій, щоб зберігати їх вручну (lazy‑write).
* **Перевизначення SQL**: перевантажте `buildInsertSql()` / `buildUpdateSql()` для UPSERT або масових операцій.

[Повернутися до змісту](../../index.md)
