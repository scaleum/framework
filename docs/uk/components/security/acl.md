[Повернутись до змісту](../../index.md)

[EN](../../../en/components/security/acl.md) | **UK** | [RU](../../../ru/components/security/acl.md)
#  Security ACL

Компонент `Security ACL` відповідає за перевірку прав доступу на рівні конкретного запису (Record ACL).
Він доповнює RBAC і дозволяє задавати права для власника, групи та інших користувачів.

##  Призначення

- Фільтрація list/select-запитів за ACL-правами
- Перевірка доступу до одного запису (`read/update/delete`)
- Єдина bitmask-модель дозволів через `Permission`
- Явна політика при відсутності ACL-рядка (`isAllowedWhenAclMissing()`)
- Fail-fast перевірка наявності ACL-таблиці

##  Основні компоненти

| Клас/Інтерфейс | Призначення |
|:----------------|:-----------|
| `Security/Permission` | Набір bitmask-констант дозволів |
| `Security/Subject` | Контекст суб’єкта (`userId`, `groupIds`, `roleIds`) |
| `Security/Contracts/AclResourceInterface` | Контракт ресурсу з ACL-таблицею та default policy |
| `Security/Contracts/AclQueryApplierInterface` | Контракт фільтрації запитів за ACL |
| `Security/Services/AclAccessQueryApplier` | Застосування ACL-умов до list/select запитів |
| `Security/Services/AclAccessResolver` | Перевірка доступу до однієї моделі |
| `Security/Services/AclTableGuard` | Перевірка існування ACL-таблиці |

##  Структура ACL-таблиці

Для кожної master-таблиці використовується окрема ACL-таблиця `*_acl`.
Зв’язок виконується по полю `record_id`.

Для суворого зв’язку 1:1 нормальний варіант, коли `record_id` одночасно:

- зовнішній ключ на master-таблицю
- первинний ключ ACL-таблиці

Це гарантує не більше однієї ACL-рядка на запис і спрощує вибірку.
Якщо в проєкті/ORM потрібен окремий surrogate key (`id`), його можна додати,
а на `record_id` залишити `UNIQUE` + `FOREIGN KEY`.

Рекомендована структура:

- `record_id`
- `owner_id`
- `group_id`
- `owner_perms`
- `group_perms`
- `other_perms`

Приклад:

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

Альтернатива з окремим `id` (якщо цього вимагає модельний шар):

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

##  Дозволи (`Permission`)

`Permission` використовує бітові прапорці (`READ`, `WRITE`, `DELETE` тощо) і може розширюватися в проєкті через наслідування.
Базові константи також винесені в `Security/Contracts/PermissionInterface`.

Перевірка виконується за маскою (режим ALL):

```php
($mask & $permission) === $permission
```

Для сценарію «хоча б одне з прав» (режим ANY):

```php
($mask & $permission) !== 0
```

Для цих сценаріїв у класі доступні helper-методи:

```php
Permission::has($mask, Permission::READ | Permission::WRITE);
Permission::hasAny($mask, Permission::READ | Permission::WRITE);
Permission::label(Permission::READ);   // Read
Permission::labels($mask);             // [bit => label, ...]
```

Обмеження по бітах:

- За замовчуванням увімкнено soft-limit `31` біт (індекси `0..30`)
- Чому не `32`: у signed `INT` старший (32-й) біт є бітом знаку,
  і його використання призводить до від’ємних значень маски.
- За потреби можна підняти ліміт до `63` (на 64-bit runtime):
- Якщо використовуєте `63` біти, поле в БД для маски має бути `BIGINT`
    (зазвичай signed `BIGINT`), а runtime PHP має бути 64-bit.

```php
Permission::setMaxBits(63);
```

Приклад розширення під проєктні політики:

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

$all = AppPermission::all(); // Актуальна повна маска з зареєстрованих бітів
```

##  Суб’єкт доступу (`Subject`)

`Subject` містить:

- `userId` — ідентифікатор користувача
- `groupIds` — групи користувача (беруть участь у Record ACL)
- `roleIds` — ролі користувача (для RBAC і суміжних задач)

У Record ACL беруть участь лише `userId` і `groupIds`.

##  Контракт ресурсу (`AclResourceInterface`)

Ресурс повідомляє ім'я ACL-таблиці та поведінку при відсутності ACL-рядка.

```php
interface AclResourceInterface
{
    public function getAclTable(): string;
    public function getAclData(): ?array;
    public function isAllowedWhenAclMissing(): bool;
}
```

`getAclData()` дозволяє повернути вже підвантажений ACL-рядок (наприклад, через `hasOne`-зв’язок моделі).
Якщо повертається `null` або порожній масив, `AclAccessResolver` виконає fallback-завантаження з ACL-таблиці.

Приклад моделі:

```php
final class Document extends ModelAbstract implements AclResourceInterface
{
    public function getAclTable(): string
    {
        return 'document_acl';
    }

    public function getAclData(): ?array
    {
        // Наприклад, якщо relation `acl` вже завантажений у модель.
        // Формат: owner_id/group_id/owner_perms/group_perms/other_perms.
        return isset($this->acl) ? $this->acl->toArray() : null;
    }

    public function isAllowedWhenAclMissing(): bool
    {
        return false;
    }
}
```

##  Фільтрація списків (`AclAccessQueryApplier`)

`AclAccessQueryApplier` додає `INNER JOIN` до ACL-таблиці та накладає умови:

- власник (`owner_id` + `owner_perms`)
- група (`group_id IN (...)` + `group_perms`)
- інші (`other_perms`)

Якщо у query builder доступний `getDatabase()`, додатково виконується fail-fast перевірка існування ACL-таблиці через `AclTableGuard`.

Приклад використання:

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

Приклад для режиму ANY (достатньо одного права):

```php
$this->aclQueryApplier->applyAny(
    $query,
    $resource,
    'd.id',
    $subject,
    Permission::READ | Permission::WRITE
);
```

##  Перевірка одного запису (`AclAccessResolver`)

`AclAccessResolver` перевіряє доступ до конкретної моделі:

1. Перевіряє, що модель реалізує `AclResourceInterface`
2. Перевіряє, що модель існує (`isExisting()`)
3. Прагне взяти ACL-дані з моделі через `getAclData()`
4. Якщо даних немає/вони порожні — перевіряє ACL-таблицю (`AclTableGuard`) і завантажує рядок за `record_id`
5. Якщо рядка немає — повертає `isAllowedWhenAclMissing()`
6. Інакше перевіряє права власника/групи/інших

Приклад:

```php
$allowed = $resolver->isAllowed($document, $subject, Permission::WRITE);
if (! $allowed) {
    throw new RuntimeException('Access denied');
}
```

Приклад для режиму ANY (достатньо одного права):

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

##  Контракт фільтрації (`AclQueryApplierInterface`)

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

Для list/select фільтрації можна передати або ACL-ресурс, або рядок з ім’ям ACL-таблиці.

`$recordField` передається явно, щоб коректно працювати з різними аліасами та join-структурами (наприклад, `d.id`, `document.id`, `id`).

##  Fail-fast перевірка таблиці (`AclTableGuard`)

`AclTableGuard` — утилітарний статичний guard.
Він перевіряє наявність ACL-таблиці і кешує факт перевірки в межах процесу.

Використання:

```php
AclTableGuard::assertTableExists($database, $tableName);
```

При відсутності таблиці викидається виключення:

```text
RuntimeException: ACL table `document_acl` is not found. Create it via migration before using ACL resource.
```

##  Межа відповідальності

- `AclAccessQueryApplier` — тільки фільтрація list/select запитів
- `AclAccessResolver` — перевірка доступу до одного запису
- `AclResourceInterface` — тільки метадані ACL-ресурсу
- `AclTableGuard` — тільки перевірка існування таблиці
- Міграції/DDL — поза ACL-сервісами, в інфраструктурному шарі проєкту

##  Ключові методи

| Клас | Метод | Призначення |
|:------|:------|:-----------|
| `AclAccessQueryApplier` | `apply(...)` | Застосувати ACL-умови до query |
| `AclAccessQueryApplier` | `applyAny(...)` | Застосувати ACL-умови в режимі ANY (хоча б одне право) |
| `AclAccessResolver` | `isAllowed(...)` | Перевірити доступ до запису |
| `AclAccessResolver` | `isAllowedAny(...)` | Перевірити доступ у режимі ANY |
| `AclAccessResolver` | `assertAllowed(...)` | Викинути виключення при відмові доступу |
| `AclAccessResolver` | `assertAllowedAny(...)` | Викинути виключення при відмові доступу в режимі ANY |
| `AclTableGuard` | `assertTableExists(...)` *(static)* | Перевірити наявність ACL-таблиці |

##  Мінімум для запуску

- [ ] Створена таблиця `*_acl`: `record_id`, `owner_id`, `group_id`, `owner_perms`, `group_perms`, `other_perms`
- [ ] Налаштовані ключі для зв’язку та пошуку (`PK/UNIQUE` за `record_id`, плюс `FOREIGN KEY` за потреби)
- [ ] Модель реалізує `AclResourceInterface` і задає policy на випадок відсутності ACL-рядка
- [ ] Для списків застосовується `AclAccessQueryApplier`, для перевірки single-record — `AclAccessResolver`
- [ ] `update/delete` виконуються лише після ACL-перевірки; `roleIds` не підмішуються в Record ACL

[Повернутися до змісту](../../index.md)



