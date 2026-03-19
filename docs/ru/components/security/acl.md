[Вернуться к оглавлению](../../index.md)

[EN](../../../en/components/security/acl.md) | [UK](../../../uk/components/security/acl.md) | **RU**
# Security ACL

Компонент `Security ACL` отвечает за проверку прав доступа на уровне конкретной записи (Record ACL).
Он дополняет RBAC и позволяет задавать права для владельца, группы и остальных пользователей.

## Назначение

- Фильтрация list/select-запросов по ACL-правам
- Проверка доступа к одной записи (`read/update/delete`)
- Единая bitmask-модель разрешений через `Permission`
- Явная политика при отсутствии ACL-строки (`isAllowedWhenAclMissing()`)
- Fail-fast проверка наличия ACL-таблицы

## Основные компоненты

| Класс/Интерфейс | Назначение |
|:----------------|:-----------|
| `Security/Permission` | Набор bitmask-констант разрешений |
| `Security/Subject` | Контекст субъекта (`userId`, `groupIds`, `roleIds`) |
| `Security/Contracts/AclResourceInterface` | Контракт ресурса с ACL-таблицей и default policy |
| `Security/Contracts/AclQueryApplierInterface` | Контракт фильтрации запросов по ACL |
| `Security/Services/AclAccessQueryApplier` | Применение ACL-условий к list/select запросам |
| `Security/Services/AclAccessResolver` | Проверка доступа к одной модели |
| `Security/Services/AclTableGuard` | Проверка существования ACL-таблицы |

## Структура ACL-таблицы

Для каждой master-таблицы используется отдельная ACL-таблица `*_acl`.
Связь выполняется по полю `record_id`.

Для строгой связи 1:1 нормальный вариант, когда `record_id` одновременно:

- внешний ключ на master-таблицу
- первичный ключ ACL-таблицы

Это гарантирует не более одной ACL-строки на запись и упрощает выборку.
Если в проекте/ORM требуется отдельный surrogate key (`id`), его можно добавить,
а на `record_id` оставить `UNIQUE` + `FOREIGN KEY`.

Рекомендуемая структура:

- `record_id`
- `owner_id`
- `group_id`
- `owner_perms`
- `group_perms`
- `other_perms`

Пример:

```sql
CREATE TABLE document_acl (
    record_id INT NOT NULL,
    owner_id INT NOT NULL,
    group_id INT NULL,
    owner_perms INT NOT NULL DEFAULT 0,
    group_perms INT NOT NULL DEFAULT 0,
    other_perms INT NOT NULL DEFAULT 0,
    PRIMARY KEY (record_id),
    CONSTRAINT fk_document_acl_record
        FOREIGN KEY (record_id) REFERENCES document(id)
        ON UPDATE CASCADE
        ON DELETE CASCADE,
    INDEX idx_document_acl_owner_id (owner_id),
    INDEX idx_document_acl_group_id (group_id)
);
```

Альтернатива с отдельным `id` (если этого требует модельный слой):

```sql
CREATE TABLE document_acl (
    id INT NOT NULL AUTO_INCREMENT,
    record_id INT NOT NULL,
    owner_id INT NOT NULL,
    group_id INT NULL,
    owner_perms INT NOT NULL DEFAULT 0,
    group_perms INT NOT NULL DEFAULT 0,
    other_perms INT NOT NULL DEFAULT 0,
    PRIMARY KEY (id),
    UNIQUE KEY uq_document_acl_record_id (record_id),
    CONSTRAINT fk_document_acl_record
        FOREIGN KEY (record_id) REFERENCES document(id)
        ON UPDATE CASCADE
        ON DELETE CASCADE,
    INDEX idx_document_acl_owner_id (owner_id),
    INDEX idx_document_acl_group_id (group_id)
);
```

## Разрешения (`Permission`)

`Permission` использует битовые флаги (`READ`, `WRITE`, `DELETE`, и т.д.) и может расширяться в проекте через наследование.
Базовые константы также вынесены в `Security/Contracts/PermissionInterface`.

Проверка выполняется по маске (режим ALL):

```php
($mask & $permission) === $permission
```

Для сценария «хотя бы одно из прав» (режим ANY):

```php
($mask & $permission) !== 0
```

Для этих сценариев в классе доступны helper-методы:

```php
Permission::has($mask, Permission::READ | Permission::WRITE);
Permission::hasAny($mask, Permission::READ | Permission::WRITE);
Permission::label(Permission::READ);   // Read
Permission::labels($mask);             // [bit => label, ...]
```

Ограничение по битам:

- По умолчанию включён soft-limit `31` бит (индексы `0..30`)
- Почему не `32`: в signed `INT` старший (32-й) бит является битом знака,
  и его использование приводит к отрицательным значениям маски.
- При необходимости можно поднять лимит до `63` (на 64-bit runtime):
- Если используете `63` бита, поле в БД для маски должно быть `BIGINT`
    (обычно signed `BIGINT`), а runtime PHP должен быть 64-bit.

```php
Permission::setMaxBits(63);
```

Пример расширения под проектные политики:

```php
final class AppPermission extends Permission
{
    public const APPROVE = 1 << 8;
    public const PUBLISH = 1 << 9;

    protected static array $perms = Permission::BASE_LABELS + [
        self::APPROVE => 'Approve',
        self::PUBLISH => 'Publish',
    ];
}

$all = AppPermission::all(); // Актуальная полная маска из зарегистрированных битов
```

## Субъект доступа (`Subject`)

`Subject` содержит:

- `userId` — идентификатор пользователя
- `groupIds` — группы пользователя (участвуют в Record ACL)
- `roleIds` — роли пользователя (для RBAC и смежных задач)

В Record ACL участвуют только `userId` и `groupIds`.

## Контракт ресурса (`AclResourceInterface`)

Ресурс сообщает имя ACL-таблицы и поведение при отсутствии ACL-строки.

```php
interface AclResourceInterface
{
    public function getAclTable(): string;
    public function getAclData(): ?array;
    public function isAllowedWhenAclMissing(): bool;
}
```

`getAclData()` позволяет вернуть уже подгруженную ACL-строку (например, через `hasOne`-связь модели).
Если возвращается `null` или пустой массив, `AclAccessResolver` выполнит fallback-загрузку из ACL-таблицы.

Пример модели:

```php
final class Document extends ModelAbstract implements AclResourceInterface
{
    public function getAclTable(): string
    {
        return 'document_acl';
    }

    public function getAclData(): ?array
    {
        // Например, если relation `acl` уже загружен в модель.
        // Формат: owner_id/group_id/owner_perms/group_perms/other_perms.
        return isset($this->acl) ? $this->acl->toArray() : null;
    }

    public function isAllowedWhenAclMissing(): bool
    {
        return false;
    }
}
```

## Фильтрация списков (`AclAccessQueryApplier`)

`AclAccessQueryApplier` добавляет `INNER JOIN` к ACL-таблице и накладывает условия:

- владелец (`owner_id` + `owner_perms`)
- группа (`group_id IN (...)` + `group_perms`)
- остальные (`other_perms`)

Если у query builder доступен `getDatabase()`, дополнительно выполняется fail-fast проверка существования ACL-таблицы через `AclTableGuard`.

Пример использования:

```php
$query = $this->getDatabase()->getQueryBuilder()
    ->select('d.*')
    ->from('document d');

$this->aclQueryApplier->apply(
    $query,
    $resource,
    'd.id',
    $subject,
    Permission::READ
);
```

Пример для режима ANY (достаточно одного права):

```php
$this->aclQueryApplier->applyAny(
    $query,
    $resource,
    'd.id',
    $subject,
    Permission::READ | Permission::WRITE
);
```

## Проверка одной записи (`AclAccessResolver`)

`AclAccessResolver` проверяет доступ к конкретной модели:

1. Проверяет, что модель реализует `AclResourceInterface`
2. Проверяет, что модель существует (`isExisting()`)
3. Пытается взять ACL-данные из модели через `getAclData()`
4. Если данных нет/они пустые — проверяет ACL-таблицу (`AclTableGuard`) и загружает строку по `record_id`
5. Если строки нет — возвращает `isAllowedWhenAclMissing()`
6. Иначе проверяет права владельца/группы/остальных

Пример:

```php
$allowed = $resolver->isAllowed($document, $subject, Permission::WRITE);
if (! $allowed) {
    throw new RuntimeException('Access denied');
}
```

Пример для режима ANY (достаточно одного права):

```php
$allowedAny = $resolver->isAllowedAny(
    $document,
    $subject,
    Permission::READ | Permission::WRITE
);

if (! $allowedAny) {
    throw new RuntimeException('Access denied');
}
```

## Контракт фильтрации (`AclQueryApplierInterface`)

```php
interface AclQueryApplierInterface
{
    public function apply(
        QueryBuilderInterface $query,
        AclResourceInterface|string $resource,
        string $recordField,
        Subject $subject,
        int $permission
    ): void;

    public function applyAny(
        QueryBuilderInterface $query,
        AclResourceInterface|string $resource,
        string $recordField,
        Subject $subject,
        int $permission
    ): void;
}
```

Для list/select фильтрации можно передать либо ACL-ресурс, либо строку с именем ACL-таблицы.

`$recordField` передаётся явно, чтобы корректно работать с разными алиасами и join-структурами (например, `d.id`, `document.id`, `id`).

## Fail-fast проверка таблицы (`AclTableGuard`)

`AclTableGuard` — утилитарный статический guard.
Он проверяет наличие ACL-таблицы и кеширует факт проверки в рамках процесса.

Использование:

```php
AclTableGuard::assertTableExists($database, $tableName);
```

При отсутствии таблицы выбрасывается исключение:

```text
RuntimeException: ACL table `document_acl` is not found. Create it via migration before using ACL resource.
```

## Граница ответственности

- `AclAccessQueryApplier` — только фильтрация list/select запросов
- `AclAccessResolver` — проверка доступа к одной записи
- `AclResourceInterface` — только метаданные ACL-ресурса
- `AclTableGuard` — только проверка существования таблицы
- Миграции/DDL — вне ACL-сервисов, в инфраструктурном слое проекта

## Ключевые методы

| Класс | Метод | Назначение |
|:------|:------|:-----------|
| `AclAccessQueryApplier` | `apply(...)` | Применить ACL-условия к query |
| `AclAccessQueryApplier` | `applyAny(...)` | Применить ACL-условия в режиме ANY (хотя бы одно право) |
| `AclAccessResolver` | `isAllowed(...)` | Проверить доступ к записи |
| `AclAccessResolver` | `isAllowedAny(...)` | Проверить доступ в режиме ANY |
| `AclAccessResolver` | `assertAllowed(...)` | Бросить исключение при отказе доступа |
| `AclAccessResolver` | `assertAllowedAny(...)` | Бросить исключение при отказе доступа в режиме ANY |
| `AclTableGuard` | `assertTableExists(...)` *(static)* | Проверить наличие ACL-таблицы |

## Минимум для запуска

- [ ] Создана `*_acl` таблица: `record_id`, `owner_id`, `group_id`, `owner_perms`, `group_perms`, `other_perms`
- [ ] Настроены ключи для связи и поиска (`PK/UNIQUE` по `record_id`, плюс `FOREIGN KEY` при необходимости)
- [ ] Модель реализует `AclResourceInterface` и задаёт policy на случай отсутствия ACL-строки
- [ ] Для списков применяется `AclAccessQueryApplier`, для single-record проверки - `AclAccessResolver`
- [ ] `update/delete` выполняются только после ACL-проверки; `roleIds` не подмешиваются в Record ACL

[Вернуться к оглавлению](../../index.md)



