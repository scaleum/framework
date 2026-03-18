[Вернуться к оглавлению](../../index.md)
# Security

Раздел `Security` в Scaleum объединяет механики аутентификации и авторизации.
Текущая страница описывает базовый auth-слой (стратегии входа, JWT, отчётность),
а детальные модели контроля доступа вынесены в подразделы [RBAC](./rbac.md) и [ACL](./acl.md).

## Назначение

- Централизованный auth-слой: аутентификация через стратегии (`AuthManager`)
- Поддержка токенов (JWT) для HTTP и CLI сценариев
- Унифицированная работа с пользователями и источниками учётных данных
- Ведение отчётов по процессу аутентификации
- Единая точка входа в смежные модели авторизации (RBAC и ACL)

## Основные компоненты

| Класс/Интерфейс | Назначение |
|:----------------|:-----------|
| `AuthManager` | Управление процессом аутентификации через набор стратегий |
| `ReportableAbstract` | Базовый класс с поддержкой отчётности |
| `Contracts/AuthenticatorInterface` | Контракт авторизатора |
| `Contracts/ReportableAuthenticatorInterface` | Контракт авторизатора с поддержкой отчётов |
| `Contracts/UserRepositoryInterface` | Контракт поиска пользователей |
| `Services/JwtManager` | Работа с JWT-токенами |
| `Supports/TokenResolver` | Извлечение токена из запроса |
| `Supports/JwtTokenPayload` | Извлечение атрибутов полезной нагрузки токена |

## Основные возможности

- Многостратегийная проверка пользователя (перебор авторизаторов)
- Поддержка токенов (`Bearer Token`, `API Token`)
- Отчётность по результатам попыток аутентификации
- Возможность использования в `CLI` и `HTTP` контексте
- Стандартизация через интерфейсы

## Смежные разделы

- [Security RBAC](./rbac.md)
- [Security ACL](./acl.md)

В разделе RBAC дополнительно описан практический pipeline подготовки `Subject`
через `SubjectMembershipLoaderInterface`, `SubjectIdsResolverInterface` и `SubjectHydrator`
с реальным примером (`user_id = 321`, default group `743`, иерархия групп).

## Интеграция RBAC + ACL: что и куда подключать

Ниже практический чек-лист для внедрения обеих моделей вместе.

1. Аутентификация (кто пользователь):
    - `AuthManager` + нужные аутентификаторы (`CredentialsAuthenticator`, `HttpJwtAuthenticator`, и т.д.)
    - результат шага: получен `userId`
2. Подготовка Subject (какие у пользователя группы и роли):
    - создайте `Subject($userId)`
    - заполните `groupIds` и `roleIds` через `SubjectHydrator` + резолверы membership (см. RBAC)
3. RBAC (можно ли в принципе выполнять действие в домене):
    - для операций уровня ресурса/типа объекта используйте `RbacAccessResolver`
        - обычно здесь проверяются coarse-grained разрешения как bitmask (`Permission::*`),
            например `Permission::READ`, `Permission::WRITE`, `Permission::DELETE`
4. ACL (можно ли работать с конкретной записью):
    - для списков: `AclAccessQueryApplier` (фильтрация выборки на уровне SQL)
    - для одной записи: `AclAccessResolver`
5. Порядок проверок в use-case:
    - сначала RBAC (быстрая проверка «вообще можно?»)
    - затем ACL (проверка ownership/group/other для конкретной записи)
6. Точки интеграции в приложении:
    - list/select endpoint: применяйте ACL-фильтрацию к query до выполнения
    - update/delete endpoint: перед изменением вызывайте `assertAllowed(...)` у RBAC и ACL
7. Данные и схема:
    - RBAC: таблица с `object_id`, `subject_type`, `subject_id`, `permissions`
    - ACL: `*_acl` таблица с `record_id`, `owner_id`, `group_id`, `owner_perms`, `group_perms`, `other_perms`
8. Кэш и консистентность:
    - при изменении RBAC-прав очищайте кэш резолвера через `clear($objectId)`
    - ACL-таблица должна создаваться миграцией до включения ACL-проверок

### Короткий сквозной flow

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

// 3) RBAC (доступ к ресурсу как таковому)
$rbacResolver->assertAllowed('document', $subject, Permission::READ);

// 4a) ACL для списка
$qb = $database->getQueryBuilder()->select('*')->from('document d');
$aclQueryApplier->apply($qb, 'document_acl', 'd.id', $subject, Permission::READ);
$rows = $database->setQuery($qb->prepare(true)->rows())->fetchAll();

// 4b) ACL для одной записи
$aclResolver->assertAllowed($documentModel, $subject, Permission::WRITE);

// 5) Безопасное изменение
// ... update/delete
```

### Когда использовать только RBAC, только ACL или оба слоя

- Только RBAC: когда права одинаковы для всего ресурса и нет record-level ограничений.
- Только ACL: когда нет ролевой модели, но нужен доступ по owner/group/other.
- RBAC + ACL: для production-сценария с разделением domain-права (RBAC) и доступа к конкретной записи (ACL).

См. подробности и контракты:
- [Security RBAC](./rbac.md)
- [Security ACL](./acl.md)

## Поддерживаемые авторизаторы

| Авторизатор | Назначение |
|:------------|:-----------|
| `Authenticators/ConsoleJwtAuthenticator` | Аутентификация в консольном окружении по JWT |
| `Authenticators/ConsoleUserIdAuthenticator` | Аутентификация в консоли по ID пользователя |
| `Authenticators/CredentialsAuthenticator` | Аутентификация по логину и паролю |
| `Authenticators/HttpJwtAuthenticator` | Аутентификация в HTTP через JWT |

## Работа с AuthManager

### Инициализация

```php
$tokenResolver = new TokenResolver();

$authManager = new AuthManager([
    new CredentialsAuthenticator($userRepository),
    new HttpJwtAuthenticator($tokenResolver, $jwtManager, $userRepository),
]);
```

### Пример попытки аутентификации
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

## Ведение отчётов
После каждой попытки можно получить отчёты (reports) о ходе аутентификации:  
```php
$errors = $authManager->getReportsByType('error');

foreach ($errors as $error) {
    echo $error['message'] . PHP_EOL;
}
```
Или проверить наличие ошибок:  
```php
if ($authManager->hasReports('error')) {
    // Ошибки присутствуют
}
```

## Работа с токенами (JWT)
#### Генерация токена
```php
$jwtManager = new JwtManager();
$token = $jwtManager->generate([
    'user_id' => 123,
]);

$payload = $jwtManager->verify($token);
$userId = $payload?->getUserId();
```

#### Извлечение токена из запроса
```php
$resolver = new TokenResolver();
$token = $resolver->resolve($_GET, $_POST, getallheaders(), $_COOKIE);
```
`TokenResolver` умеет искать токен:
- в `$_GET`, `$_POST`, `$_COOKIE`
- в HTTP-заголовках
- в поле `Authorization: Bearer <token>`


## Методы класса `AuthManager`
Метод | Назначение
|:------------|:-----------|
`authenticate(array $credentials, array $headers = [], bool $verbose = false): ?AuthenticatableInterface` | Аутентификация через стратегии
`getReportsByType(string $type = 'debug'): array` | Получить отчёты по типу
`hasReports(string $type = 'debug'): bool` | Проверить наличие отчётов


## Методы класса `TokenResolver`
Метод | Назначение
|:------------|:-----------|
`resolve(array $get, array $post, array $headers, array $cookies = []): ?string` | Найти токен в запросе
`fromServer(array $server): array` | Преобразовать массив $_SERVER в HTTP-заголовки
`isServerHeaders(array $headers): bool` | Проверить, являются ли заголовки серверными


## Пример полного цикла
```php
$jwtManager = new JwtManager();
$tokenResolver = new TokenResolver();
$userRepository = new UserRepository();

$authManager = new AuthManager([
    new HttpJwtAuthenticator($tokenResolver, $jwtManager, $userRepository),
    new CredentialsAuthenticator($userRepository),
]);

// Извлечь токен
$token = $tokenResolver->resolve($_GET, $_POST, getallheaders(), $_COOKIE);

// Аутентифицировать пользователя
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
## Ошибки
Исключение | Условие
|:---|:---|
`RuntimeException` | `assertAllowed(...)`/`assertAllowedAny(...)` в ACL/RBAC при отказе доступа, а также при отсутствии ACL-таблицы

Примечание: методы аутентификаторов обычно не выбрасывают исключения, а возвращают `null` и пишут детали в отчёты (`getReportsByType('error')`).

[Вернуться к оглавлению](../../index.md)



