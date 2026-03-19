[Повернутись до змісту](../index.md)

[EN](../../en/components/service-locator.md) | **UK** | [RU](../../ru/components/service-locator.md)
# Service Manager

Компонент фреймворку Scaleum для управління сервісами складається з двох частин:
- `ServiceManager` — контейнер сервісів з можливістю лінивого створення об'єктів.
- `ServiceLocator` — глобальний фасад для доступу до сервісів.


## Призначення

- Реєстрація сервісів
- Ліниве створення екземплярів через рефлексію
- Підтримка конфігурацій та залежностей
- Глобальний доступ до сервісів через фасад
- Робота в суворому режимі з контролем помилок

## Основні можливості

- Реєстрація класів, об'єктів, масивів конфігурацій
- Вирішення залежностей при створенні сервісів
- Автоматична ініціалізація сервісів (`eager`)
- Глобальний доступ через `ServiceLocator`
- Управління суворим режимом (`strictMode`)

## Реєстрація сервісів

### Реєстрація класу провайдера

```php
ServiceLocator::setProvider(new ServiceManager());
ServiceLocator::set('mailer', Mailer::class);
```
\* - в рамках виконання основного потоку ядра(`KernelAbstract`) в `ServiceLocator` автоматично реєструється екземпляр `ServiceManager::class` як провайдер сервісів, одночасно доступний у контейнері під псевдонімом `Framework::SVC_POOL`.
 
### Реєстрація об'єкта
```php
$mailer = new Mailer('smtp.example.com');
ServiceLocator::set('mailer', $mailer);
```

### Реєстрація через масив конфігурації
```php
ServiceLocator::set('db', [
    'class' => DatabaseConnection::class,
    'host'  => 'localhost',
    'port'  => 3306,
]);

ServiceLocator::set('translator',[
    'class'  => Scaleum\i18n\Translator::class,
    'config' => [
        // 'dependencies' => [
        //     'events'  => Framework::SVC_EVENTS, // для всіх сервісів
        //     'session' => 'session',
        // ],
        // 'locale'        => 'en_US',
        // 'text_domain'   => 'default',
        // 'translation_dir' => __DIR__ . '/../../locale',
    ],
]);
```

### Автоматична ініціалізація (eager)
```php
ServiceLocator::set('cache', [
    'class' => CacheService::class,
    'eager' => true,
]);
```
Сервіс буде створений негайно при реєстрації.

### Отримання сервісів
```php
$mailer = ServiceLocator::get('mailer');
$mailer->send('Welcome email');
```
Якщо сервіс ще не створений, `ServiceManager` створить його автоматично.

### Робота із залежностями  
Можна визначити залежності інших сервісів:  
```php
ServiceLocator::set('reportService', [
    'class' => ReportService::class,
    'dependencies' => [
        'mailer' => 'mailer', // буде підставлений сервіс mailer
    ],
]);
```
При створенні `ReportService` залежності будуть автоматично вирішені через `getService()`.

## Методи `ServiceManager`
Метод | Призначення
|:------|:-----------|
| getService(string $name, mixed $default = null) | Отримати сервіс |
| setService(string $name, mixed $definition, bool $override = false) | Зареєструвати сервіс |
| hasService(string $name): bool | Перевірити наявність сервісу |
| unlink(string $name): self | Видалити сервіс |
| getAll(): array | Отримати всі створені сервіси |

## Методи `ServiceLocator`

Метод | Призначення
|:------|:-----------|
setProvider(ServiceProviderInterface $instance) | Встановити контейнер сервісів
getProvider(): ?ServiceProviderInterface | Отримати контейнер
strictModeOn(), strictModeOff() | Управління суворим режимом
get(string $name, mixed $default = null) | Отримати сервіс
set(string $name, mixed $definition, bool $override = false) | Зареєструвати сервіс
has(string $name): bool | Перевірити наявність сервісу
getAll(): array | Отримати всі сервіси


### Приклад повного використання
```php
// Встановлення провайдера
$manager = new ServiceManager();
ServiceLocator::setProvider($manager);

// Реєстрація сервісів
ServiceLocator::set('db', [
    'class' => DatabaseConnection::class,
    'host'  => 'localhost',
    'port'  => 3306,
]);

ServiceLocator::set('mailer', new Mailer('smtp.example.com'));

// Отримання та використання
$db = ServiceLocator::get('db');
$mailer = ServiceLocator::get('mailer');

$mailer->send('Test email');
```

## Робота суворого режиму
За замовчуванням увімкнено суворий режим:  
- Якщо провайдер не встановлений — викидається `ERuntimeError`.
- Можна вимкнути суворий режим для більш м'якої обробки помилок:
```php
ServiceLocator::strictModeOff();
$mailer = ServiceLocator::get('mailer', new NullMailer());
```

## Помилки
Виняток | Умова
|:------|:-----------|
`ERuntimeError` | Немає провайдера в суворому режимі, або не знайдено обов'язковий параметр конструктора
`EComponentError` | Помилка при реєстрації неправильного сервісу
`ENotFoundError` | Клас сервісу не знайдено

[Повернутись до змісту](../index.md)