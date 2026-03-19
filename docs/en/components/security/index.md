[Back to Contents](../../index.md)

**EN** | [UK](../../../uk/components/security/index.md) | [RU](../../../ru/components/security/index.md)
#  Security

The `Security` section in Scaleum combines authentication and authorization mechanics.
This page describes the basic auth layer (login strategies, JWT, reporting),
while detailed access control models are moved to the subsections [RBAC](./rbac.md) and [ACL](./acl.md).

##  Purpose

- Centralized auth layer: authentication via strategies (`AuthManager`)
- Support for tokens (JWT) for HTTP and CLI scenarios
- Unified handling of users and credential sources
- Reporting on the authentication process
- Single entry point to related authorization models (RBAC and ACL)

##  Main Components

| Class/Interface | Purpose |
|:----------------|:--------|
| `AuthManager` | Manages the authentication process through a set of strategies |
| `ReportableAbstract` | Base class with reporting support |
| `Contracts/AuthenticatorInterface` | Authenticator contract |
| `Contracts/ReportableAuthenticatorInterface` | Authenticator contract with reporting support |
| `Contracts/UserRepositoryInterface` | User lookup contract |
| `Services/JwtManager` | JWT token handling |
| `Supports/TokenResolver` | Extracting token from request |
| `Supports/JwtTokenPayload` | Extracting token payload attributes |

##  Key Features

- Multi-strategy user verification (iterating authenticators)
- Token support (`Bearer Token`, `API Token`)
- Reporting on authentication attempt results
- Usable in both `CLI` and `HTTP` contexts
- Standardization through interfaces

##  Related Sections

- [Security RBAC](./rbac.md)
- [Security ACL](./acl.md)

The RBAC section additionally describes a practical pipeline for preparing the `Subject`
via `SubjectMembershipLoaderInterface`, `SubjectIdsResolverInterface`, and `SubjectHydrator`
with a real example (`user_id = 321`, default group `743`, group hierarchy).

##  RBAC + ACL Integration: what and where to connect

Below is a practical checklist for implementing both models together.

1. Authentication (who is the user):
    - `AuthManager` + required authenticators (`CredentialsAuthenticator`, `HttpJwtAuthenticator`, etc.)
    - step result: obtained `userId`
2. Preparing Subject (which groups and roles the user has):
    - create `Subject($userId)`
    - fill `groupIds` and `roleIds` via `SubjectHydrator` + membership resolvers (see RBAC)
3. RBAC (whether the action is allowed in the domain):
    - for resource-level/object-type operations use `RbacAccessResolver`
        - usually coarse-grained permissions are checked here as bitmask (`Permission::*`),
            e.g. `Permission::READ`, `Permission::WRITE`, `Permission::DELETE`
4. ACL (whether working with a specific record is allowed):
    - for lists: `AclAccessQueryApplier` (filtering selection at SQL level)
    - for a single record: `AclAccessResolver`
5. Check order in use-case:
    - first RBAC (quick check "is it allowed at all?")
    - then ACL (check ownership/group/other for the specific record)
6. Integration points in the application:
    - list/select endpoint: apply ACL filtering to query before execution
    - update/delete endpoint: before modification call `assertAllowed(...)` on RBAC and ACL
7. Data and schema:
    - RBAC: table with `object_id`, `subject_type`, `subject_id`, `permissions`
    - ACL: `*_acl` table with `record_id`, `owner_id`, `group_id`, `owner_perms`, `group_perms`, `other_perms`
8. Cache and consistency:
    - when RBAC permissions change, clear resolver cache via `clear($objectId)`
    - ACL table must be created by migration before enabling ACL checks

###  Short end-to-end flow

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

// 3) RBAC (access to the resource as such)
$rbacResolver->assertAllowed('document', $subject, Permission::READ);

// 4a) ACL for list
$qb = $database->getQueryBuilder()->select('*')->from('document d');
$aclQueryApplier->apply($qb, 'document_acl', 'd.id', $subject, Permission::READ);
$rows = $database->setQuery($qb->prepare(true)->rows())->fetchAll();

// 4b) ACL for single record
$aclResolver->assertAllowed($documentModel, $subject, Permission::WRITE);

// 5) Safe modification
// ... update/delete
```

###  When to use only RBAC, only ACL, or both layers

- Only RBAC: when permissions are the same for the entire resource and there are no record-level restrictions.
- Only ACL: when there is no role model but access by owner/group/other is needed.
- RBAC + ACL: for production scenarios with separation of domain permissions (RBAC) and access to specific records (ACL).

See details and contracts:
- [Security RBAC](./rbac.md)
- [Security ACL](./acl.md)

##  Supported Authenticators

| Authenticator | Purpose |
|:------------|:-----------|
| `Authenticators/ConsoleJwtAuthenticator` | Authentication in console environment via JWT |
| `Authenticators/ConsoleUserIdAuthenticator` | Authentication in console by user ID |
| `Authenticators/CredentialsAuthenticator` | Authentication by login and password |
| `Authenticators/HttpJwtAuthenticator` | Authentication in HTTP via JWT |

##  Working with AuthManager

###  Initialization

```php
$tokenResolver = new TokenResolver();

$authManager = new AuthManager([
    new CredentialsAuthenticator($userRepository),
    new HttpJwtAuthenticator($tokenResolver, $jwtManager, $userRepository),
]);
```

###  Example of an authentication attempt
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

##  Reporting
After each attempt, you can get reports about the authentication process:  
```php
$errors = $authManager->getReportsByType('error');

foreach ($errors as $error) {
    echo $error['message'] . PHP_EOL;
}
```
Or check for the presence of errors:  
```php
if ($authManager->hasReports('error')) {
    // Errors are present
}
```

##  Working with tokens (JWT)
####  Token generation
```php
$jwtManager = new JwtManager();
$token = $jwtManager->generate([
    'user_id' => 123,
]);

$payload = $jwtManager->verify($token);
$userId = $payload?->getUserId();
```

####  Extracting token from request
```php
$resolver = new TokenResolver();
$token = $resolver->resolve($_GET, $_POST, getallheaders(), $_COOKIE);
```
`TokenResolver` can search for the token:
- in `$_GET`, `$_POST`, `$_COOKIE`
- in HTTP headers
- in the `Authorization: Bearer <token>` field


##  Methods of the `AuthManager` class
Method | Purpose
|:------------|:-----------|
`authenticate(array $credentials, array $headers = [], bool $verbose = false): ?AuthenticatableInterface` | Authenticate via strategies
`getReportsByType(string $type = 'debug'): array` | Get reports by type
`hasReports(string $type = 'debug'): bool` | Check for presence of reports


##  Methods of the `TokenResolver` class
Method | Purpose
|:------------|:-----------|
`resolve(array $get, array $post, array $headers, array $cookies = []): ?string` | Find token in request
`fromServer(array $server): array` | Convert $_SERVER array to HTTP headers
`isServerHeaders(array $headers): bool` | Check if headers are server headers


##  Example of a full cycle
```php
$jwtManager = new JwtManager();
$tokenResolver = new TokenResolver();
$userRepository = new UserRepository();

$authManager = new AuthManager([
    new HttpJwtAuthenticator($tokenResolver, $jwtManager, $userRepository),
    new CredentialsAuthenticator($userRepository),
]);

// Extract token
$token = $tokenResolver->resolve($_GET, $_POST, getallheaders(), $_COOKIE);

// Authenticate user
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
##  Errors
Exception | Condition
|:---|:---|
`RuntimeException` | `assertAllowed(...)`/`assertAllowedAny(...)` in ACL/RBAC on access denial, as well as when ACL table is missing

Note: authenticator methods usually do not throw exceptions but return `null` and write details to reports (`getReportsByType('error')`).

[Return to contents](../../index.md)



