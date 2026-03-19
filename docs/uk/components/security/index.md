[Повернутися до змісту](../../index.md)

[EN](../../../en/components/security/index.md) | **UK** | [RU](../../../ru/components/security/index.md)
#  Безпека

Розділ `Security` у Scaleum об'єднує механіки автентифікації та авторизації.
Поточна сторінка описує базовий auth-шар (стратегії входу, JWT, звітність),
а детальні моделі контролю доступу винесені у підрозділи [RBAC](./rbac.md) та [ACL](./acl.md).

##  Призначення

- Централізований auth-шар: автентифікація через стратегії (`AuthManager`)
- Підтримка токенів (JWT) для HTTP та CLI сценаріїв
- Уніфікована робота з користувачами та джерелами облікових даних
- Ведення звітів по процесу автентифікації
- Єдина точка входу у суміжні моделі авторизації (RBAC та ACL)

##  Основні компоненти

| Клас/Інтерфейс | Призначення |
|:----------------|:-----------|
| `AuthManager` | Управління процесом автентифікації через набір стратегій |
| `ReportableAbstract` | Базовий клас з підтримкою звітності |
| `Contracts/AuthenticatorInterface` | Контракт авторизатора |
| `Contracts/ReportableAuthenticatorInterface` | Контракт авторизатора з підтримкою звітів |
| `Contracts/UserRepositoryInterface` | Контракт пошуку користувачів |
| `Services/JwtManager` | Робота з JWT-токенами |
| `Supports/TokenResolver` | Витягування токена з запиту |
| `Supports/JwtTokenPayload` | Витягування атрибутів корисного навантаження токена |

##  Основні можливості

- Багатостратегічна перевірка користувача (перебір авторизаторів)
- Підтримка токенів (`Bearer Token`, `API Token`)
- Звітність за результатами спроб автентифікації
- Можливість використання у `CLI` та `HTTP` контексті
- Стандартизація через інтерфейси

##  Суміжні розділи

- [Security RBAC](./rbac.md)
- [Security ACL](./acl.md)

У розділі RBAC додатково описано практичний pipeline підготовки `Subject`
через `SubjectMembershipLoaderInterface`, `SubjectIdsResolverInterface` та `SubjectHydrator`
з реальним прикладом (`user_id = 321`, default group `743`, ієрархія груп).

##  Інтеграція RBAC + ACL: що і куди підключати

Нижче практичний чек-лист для впровадження обох моделей разом.

1. Автентифікація (хто користувач):
    - `AuthManager` + потрібні автентифікатори (`CredentialsAuthenticator`, `HttpJwtAuthenticator` тощо)
    - результат кроку: отримано `userId`
2. Підготовка Subject (які у користувача групи та ролі):
    - створіть `Subject($userId)`
    - заповніть `groupIds` та `roleIds` через `SubjectHydrator` + резолвери membership (див. RBAC)
3. RBAC (чи можна взагалі виконувати дію в домені):
    - для операцій рівня ресурсу/типу об’єкта використовуйте `RbacAccessResolver`
        - зазвичай тут перевіряються coarse-grained дозволи як bitmask (`Permission::*`),
            наприклад `Permission::READ`, `Permission::WRITE`, `Permission::DELETE`
4. ACL (чи можна працювати з конкретним записом):
    - для списків: `AclAccessQueryApplier` (фільтрація вибірки на рівні SQL)
    - для одного запису: `AclAccessResolver`
5. Порядок перевірок у use-case:
    - спочатку RBAC (швидка перевірка «взагалі можна?»)
    - потім ACL (перевірка ownership/group/other для конкретного запису)
6. Точки інтеграції в додатку:
    - list/select endpoint: застосовуйте ACL-фільтрацію до query до виконання
    - update/delete endpoint: перед зміною викликайте `assertAllowed(...)` у RBAC та ACL
7. Дані та схема:
    - RBAC: таблиця з `object_id`, `subject_type`, `subject_id`, `permissions`
    - ACL: `*_acl` таблиця з `record_id`, `owner_id`, `group_id`, `owner_perms`, `group_perms`, `other_perms`
8. Кеш і консистентність:
    - при зміні RBAC-прав очищайте кеш резолвера через `clear($objectId)`
    - ACL-таблиця має створюватися міграцією до включення ACL-перевірок

###  Короткий наскрізний flow

```php
// 1) Auth
$user = $authManager->authenticate($credentials, $headers, verbose: true);
if ($user === null) {
     // 401
}

// 2) Subject
$subject = new Subject((int) $user->getId());
$subjectHydrator->hydrateGroupIdsForUser($subject, $groupResolver, [743]);
$subjectHydrator->hydrateRoleIdsForUser($subject, $roleResolver);

// 3) RBAC (доступ до ресурсу як такого)
$rbacResolver->assertAllowed('document', $subject, Permission::READ);

// 4a) ACL для списку
$qb = $database->getQueryBuilder()->select('*')->from('document d');
$aclQueryApplier->apply($qb, 'document_acl', 'd.id', $subject, Permission::READ);
$rows = $database->setQuery($qb->prepare(true)->rows())->fetchAll();

// 4b) ACL для одного запису
$aclResolver->assertAllowed($documentModel, $subject, Permission::WRITE);

// 5) Безпечне змінення
// ... update/delete
```

###  Коли використовувати тільки RBAC, тільки ACL або обидва шари

- Тільки RBAC: коли права однакові для всього ресурсу і немає record-level обмежень.
- Тільки ACL: коли немає ролевої моделі, але потрібен доступ по owner/group/other.
- RBAC + ACL: для production-сценарію з розділенням domain-прав (RBAC) і доступу до конкретного запису (ACL).

Див. деталі та контракти:
- [Security RBAC](./rbac.md)
- [Security ACL](./acl.md)

##  Підтримувані авторизатори

| Авторизатор | Призначення |
|:------------|:-----------|
| `Authenticators/ConsoleJwtAuthenticator` | Автентифікація в консольному оточенні за JWT |
| `Authenticators/ConsoleUserIdAuthenticator` | Автентифікація в консолі за ID користувача |
| `Authenticators/CredentialsAuthenticator` | Автентифікація за логіном і паролем |
| `Authenticators/HttpJwtAuthenticator` | Автентифікація в HTTP через JWT |

##  Робота з AuthManager

###  Ініціалізація

```php
$tokenResolver = new TokenResolver();

$authManager = new AuthManager([
    new CredentialsAuthenticator($userRepository),
    new HttpJwtAuthenticator($tokenResolver, $jwtManager, $userRepository),
]);
```

###  Приклад спроби автентифікації
```php
$user = $authManager->authenticate(
    credentials: ['identity' => 'admin', 'password' => 'secret'],
    headers: getallheaders(),
    verbose: true
);

if ($user !== null) {
    echo "User authenticated";
} else {
    echo "Authentication failed";
}
```

##  Ведення звітів
Після кожної спроби можна отримати звіти (reports) про хід автентифікації:  
```php
$errors = $authManager->getReportsByType('error');

foreach ($errors as $error) {
    echo $error['message'] . PHP_EOL;
}
```
Або перевірити наявність помилок:  
```php
if ($authManager->hasReports('error')) {
    // Помилки присутні
}
```

##  Робота з токенами (JWT)
####  Генерація токена
```php
$jwtManager = new JwtManager();
$token = $jwtManager->generate([
    'user_id' => 123,
]);

$payload = $jwtManager->verify($token);
$userId = $payload?->getUserId();
```

####  Витяг токена з запиту
```php
$resolver = new TokenResolver();
$token = $resolver->resolve($_GET, $_POST, getallheaders(), $_COOKIE);
```
`TokenResolver` вміє шукати токен:
- у `$_GET`, `$_POST`, `$_COOKIE`
- у HTTP-заголовках
- у полі `Authorization: Bearer <token>`


##  Методи класу `AuthManager`
Метод | Призначення
|:------------|:-----------|
`authenticate(array $credentials, array $headers = [], bool $verbose = false): ?AuthenticatableInterface` | Автентифікація через стратегії
`getReportsByType(string $type = 'debug'): array` | Отримати звіти за типом
`hasReports(string $type = 'debug'): bool` | Перевірити наявність звітів


##  Методи класу `TokenResolver`
Метод | Призначення
|:------------|:-----------|
`resolve(array $get, array $post, array $headers, array $cookies = []): ?string` | Знайти токен у запиті
`fromServer(array $server): array` | Перетворити масив $_SERVER у HTTP-заголовки
`isServerHeaders(array $headers): bool` | Перевірити, чи є заголовки серверними


##  Приклад повного циклу
```php
$jwtManager = new JwtManager();
$tokenResolver = new TokenResolver();
$userRepository = new UserRepository();

$authManager = new AuthManager([
    new HttpJwtAuthenticator($tokenResolver, $jwtManager, $userRepository),
    new CredentialsAuthenticator($userRepository),
]);

// Витягти токен
$token = $tokenResolver->resolve($_GET, $_POST, getallheaders(), $_COOKIE);

// Автентифікувати користувача
$user = $authManager->authenticate(
    ['token' => $token],
    getallheaders(),
    verbose: true
);

if ($user) {
    echo "Authenticated";
} else {
    foreach ($authManager->getReportsByType('error') as $error) {
        echo "Auth error: " . $error['message'] . PHP_EOL;
    }
}
```
##  Помилки
Виняток | Умова
|:---|:---|
`RuntimeException` | `assertAllowed(...)`/`assertAllowedAny(...)` в ACL/RBAC при відмові в доступі, а також при відсутності ACL-таблиці

Примітка: методи авторизаторів зазвичай не викидають винятки, а повертають `null` і записують деталі у звіти (`getReportsByType('error')`).

[Повернутися до змісту](../../index.md)



