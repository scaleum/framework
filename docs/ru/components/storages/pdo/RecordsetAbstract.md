[Вернуться к оглавлению](../../index.md)

[EN](../../../../en/components/storages/pdo/RecordsetAbstract.md) | [UK](../../../../uk/components/storages/pdo/RecordsetAbstract.md) | **RU**
# RecordsetAbstract

`RecordsetAbstract` — базовый класс для коллекций записей в PDO-слое Scaleum.
Он умеет:

- загружать записи из SQL-запроса (`load()`),
- гидрировать строки БД в модели (`$modelClass`),
- фильтровать записи в памяти (`filter()`),
- отслеживать удаленные записи и сохранять изменения (`save()`).

Класс реализует `IteratorAggregate`, поэтому его можно использовать в `foreach`.

---

## Когда использовать

`RecordsetAbstract` подходит, когда нужно:

- инкапсулировать SQL выборки в отдельный объект-коллекцию,
- применять пост-обработку/фильтрацию уже загруженных данных,
- работать с набором `ModelInterface`-объектов и выполнять batch-update/delete.

---

## Минимальная реализация

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

Использование:

```php
<?php

$recordset = (new UserRecordset($database))
    ->setParams([':limit' => 50, ':offset' => 0])
    ->load();

foreach ($recordset as $row) {
    // $row — массив или модель, зависит от $modelClass
}

$total = $recordset->getRecordTotalCount();
```

---

## Основные методы

| Метод | Назначение |
| --- | --- |
| `load()` | Выполняет запрос `getQuery()` и заполняет recordset. |
| `filter(callable $callback)` | Возвращает новый экземпляр recordset с отфильтрованными элементами. |
| `add(mixed $record)` | Добавляет ассоциативный массив или `ModelInterface`. |
| `remove(ModelInterface $model)` | Удаляет модель по primary key и кладет ее в список removed. |
| `removeByIndex(int $index)` | Удаляет элемент коллекции по индексу. |
| `save()` | Вызывает `delete()` для removed и `update()` для текущих моделей. |
| `toArray()` | Преобразует коллекцию в массив (для моделей вызывает `toArray()`). |

---

## ACL-фильтры: примеры

Ниже примеры в двух режимах:

- фильтрация после загрузки (в памяти),
- фильтрация на уровне SQL-запроса через `AclAccessQueryApplier`.

### 1. ACL-фильтрация в памяти через `filter(...)`

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

Этот подход удобен, если данные уже загружены и нужно применить дополнительный ACL-контекст на уровне приложения.

### 2. ACL-фильтрация в SQL (`apply`) для режима "все права"

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

В режиме `apply(...)` запись проходит, только если набор требуемых битов присутствует полностью.

### 3. ACL-фильтрация в SQL (`applyAny`) для режима "любое право"

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

`applyAny(...)` пропускает записи, где есть хотя бы один из требуемых битов.

---

## Рекомендации

- Для тяжелых ACL-сценариев предпочитайте SQL-фильтрацию (`apply`/`applyAny`), чтобы уменьшить объем данных в памяти.
- `filter(...)` используйте для локальных/пост-правил после основной SQL-фильтрации.
- Если передан `$modelClass`, следите, чтобы записи были ассоциативными массивами и содержали ключи, ожидаемые `ModelAbstract::load(...)`.

[Вернуться к оглавлению](../../index.md)
