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



