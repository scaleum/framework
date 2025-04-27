[Вернуться к оглавлению](../index.md)
# Security

Компонент `Security` в Scaleum обеспечивает централизованную аутентификацию пользователей через различные стратегии авторизации.

## Назначение

- Многостратегийная аутентификация (`AuthManager`)
- Поддержка токенов (JWT)
- Унифицированная работа с пользователями
- Ведение отчётов по процессу аутентификации
- Гибкая интеграция в консольные и HTTP-приложения

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
$authManager = new AuthManager([
    new CredentialsAuthenticator($userRepository),
    new HttpJwtAuthenticator($jwtManager),
]);
```

### Пример попытки аутентификации
```php
$user = $authManager->authenticate(
    credentials: ['username' => 'admin', 'password' => 'secret'],
    headers: getallheaders(),
    verbose: true
);

if ($user !== null) {
    echo "User authenticated: " . $user->getIdentity();
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
$payload = new JwtTokenPayload(['user_id' => 123]);
$token = $jwtManager->encode($payload);
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
$userRepository = new UserRepository();
$authManager = new AuthManager([
    new HttpJwtAuthenticator($jwtManager),
    new CredentialsAuthenticator($userRepository),
]);

// Извлечь токен
$resolver = new TokenResolver();
$token = $resolver->resolve($_GET, $_POST, getallheaders());

// Аутентифицировать пользователя
$user = $authManager->authenticate(
    ['token' => $token],
    getallheaders(),
    verbose: true
);

if ($user) {
    echo "Authenticated as " . $user->getIdentity();
} else {
    foreach ($authManager->getReportsByType('error') as $error) {
        echo "Auth error: " . $error['message'] . PHP_EOL;
    }
}
```
## Ошибки
Исключение | Условие
|:---|:---|
`ERuntimeError`, `InvalidArgumentException` | Ошибки при работе с токенами или авторизацией

[Вернуться к оглавлению](../index.md)