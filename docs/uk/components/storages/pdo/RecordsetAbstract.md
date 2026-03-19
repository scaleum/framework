[Вернутися до змісту](../../index.md)

[EN](../../../../en/components/storages/pdo/RecordsetAbstract.md) | **UK** | [RU](../../../../ru/components/storages/pdo/RecordsetAbstract.md)
#  RecordsetAbstract

`RecordsetAbstract` — базовий клас для колекцій записів у PDO-шарі Scaleum.
Він вміє:

- завантажувати записи з SQL-запиту (`load()`),
- гідрувати рядки БД у моделі (`$modelClass`),
- фільтрувати записи в пам’яті (`filter()`),
- відслідковувати видалені записи та зберігати зміни (`save()`).

Клас реалізує `IteratorAggregate`, тому його можна використовувати в `foreach`.

---

##  Коли використовувати

`RecordsetAbstract` підходить, коли потрібно:

- інкапсулювати SQL вибірки в окремий об’єкт-колекцію,
- застосовувати пост-обробку/фільтрацію вже завантажених даних,
- працювати з набором `ModelInterface`-об’єктів і виконувати batch-update/delete.

---

##  Мінімальна реалізація

```php
<?php

declare(strict_types=1);

namespace App\Storage;

use Scaleum\Storages\PDO\RecordsetAbstract;

final class UserRecordset extends RecordsetAbstract
{
    protected function getQuery(): string
    {
        return 'SELECT id, email, status FROM users ORDER BY id DESC LIMIT :limit OFFSET :offset';
    }
}
```

Використання:

```php
<?php

$recordset = (new UserRecordset($database))
    ->setParams([':limit' => 50, ':offset' => 0])
    ->load();

foreach ($recordset as $row) {
    // $row — масив або модель, залежить від $modelClass
}

$total = $recordset->getRecordTotalCount();
```

---

##  Основні методи

| Метод | Призначення |
| --- | --- |
| `load()` | Виконує запит `getQuery()` і заповнює recordset. |
| `filter(callable $callback)` | Повертає новий екземпляр recordset з відфільтрованими елементами. |
| `add(mixed $record)` | Додає асоціативний масив або `ModelInterface`. |
| `remove(ModelInterface $model)` | Видаляє модель за primary key і кладе її в список removed. |
| `removeByIndex(int $index)` | Видаляє елемент колекції за індексом. |
| `save()` | Викликає `delete()` для removed і `update()` для поточних моделей. |
| `toArray()` | Перетворює колекцію в масив (для моделей викликає `toArray()`). |

---

##  ACL-фільтри: приклади

Нижче приклади у двох режимах:

- фільтрація після завантаження (в пам’яті),
- фільтрація на рівні SQL-запиту через `AclAccessQueryApplier`.

###  1. ACL-фільтрація в пам’яті через `filter(...)`

```php
<?php

use Scaleum\Security\Permission;
use Scaleum\Security\Subject;

$subject = new Subject(userId: 10, groupIds: [2, 3]);
$required = Permission::READ | Permission::WRITE;

$visible = $recordset->filter(function (array $row) use ($subject, $required): bool {
    $isOwner = (int) ($row['owner_id'] ?? 0) === $subject->getUserId();
    if ($isOwner) {
        return (((int) ($row['owner_perms'] ?? 0)) & $required) === $required;
    }

    $groupId = (int) ($row['group_id'] ?? 0);
    if (in_array($groupId, $subject->getGroupIds(), true)) {
        return (((int) ($row['group_perms'] ?? 0)) & $required) === $required;
    }

    return (((int) ($row['other_perms'] ?? 0)) & $required) === $required;
});
```

Цей підхід зручний, якщо дані вже завантажені і потрібно застосувати додатковий ACL-контекст на рівні застосунку.

###  2. ACL-фільтрація в SQL (`apply`) для режиму "всі права"

```php
<?php

declare(strict_types=1);

namespace App\Storage;

use Scaleum\Security\Contracts\AclResourceInterface;
use Scaleum\Security\Permission;
use Scaleum\Security\Services\AclAccessQueryApplier;
use Scaleum\Security\Subject;
use Scaleum\Storages\PDO\RecordsetAbstract;

final class DocumentRecordset extends RecordsetAbstract
{
    public function __construct(
        private readonly Subject $subject,
        private readonly AclResourceInterface|string $aclResource,
        ?\Scaleum\Storages\PDO\Database $database = null,
        ?string $modelClass = null,
        array $records = []
    ) {
        parent::__construct($database, $modelClass, $records);
    }

    protected function getQuery(): string
    {
        $query = $this->getDatabase()->getQueryBuilder();
        $query->select('d.*')->from('documents AS d');

        (new AclAccessQueryApplier())->apply(
            query: $query,
            resource: $this->aclResource,
            recordField: 'd.id',
            subject: $this->subject,
            permission: Permission::READ | Permission::WRITE
        );

        return (string) $query;
    }
}
```

У режимі `apply(...)` запис проходить, лише якщо набір потрібних бітів присутній повністю.

###  3. ACL-фільтрація в SQL (`applyAny`) для режиму "будь-яке право"

```php
<?php

$applier = new AclAccessQueryApplier();
$applier->applyAny(
    query: $query,
    resource: 'document_acl',
    recordField: 'd.id',
    subject: $subject,
    permission: Permission::READ | Permission::WRITE
);
```

`applyAny(...)` пропускає записи, де є хоча б один із потрібних бітів.

---

##  Рекомендації

- Для важких ACL-сценаріїв надавайте перевагу SQL-фільтрації (`apply`/`applyAny`), щоб зменшити обсяг даних у пам’яті.
- `filter(...)` використовуйте для локальних/пост-правил після основної SQL-фільтрації.
- Якщо передано `$modelClass`, слідкуйте, щоб записи були асоціативними масивами і містили ключі, очікувані `ModelAbstract::load(...)`.

[Повернутися до змісту](../../index.md)
