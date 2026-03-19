[Назад](./application.md) | [Повернутися до змісту](../../index.md)

[EN](../../../en/components/console/commands.md) | **UK** | [RU](../../../ru/components/console/commands.md)
# Commands

Клас `Scaleum\Console\DependencyInjection\Commands` — конфігуратор контейнера залежностей для модуля консольних команд. Реєструє сервіси та параметри, необхідні для пошуку та запуску CLI-команд.

## Призначення

- Визначити в контейнері сервіси та налаштування для консольного режиму.
- Налаштувати диспетчер команд (`CommandDispatcher`).
- Вказати шляхи до файлів і директорій з описом команд.

## Зв’язок з ядром

Використовується в `Console\Application::bootstrap()`, де додається до реєстру конфігураторів ядра:
```php
$this->getRegistry()->set('kernel.configurators', [
    new Commands(),
]);
```  
Таким чином при `KernelAbstract::bootstrap()` викликається `Commands::configure()`, і контейнер отримує визначення для CLI-команди.

## Основні завдання

- Реєстрація класу `CommandDispatcher` для маршрутизації та виконання команд.
- Впровадження параметрів:
  - `commands.file`  — шлях до файлу `commands.php` у директорії конфига ядра.
  - `commands.directory` — директорія з додатковими файлами команд.
  - `commands.dispatcher` — псевдонім сервісу диспетчера команд.

## Визначення в контейнері

| Ім'я в контейнері             | Значення / Сервіс                                         | Опис                                                          |
|:-----------------------------|:----------------------------------------------------------|:--------------------------------------------------------------|
| `CommandDispatcher::class`   | `Autowire::create()`                                      | Автоматична автоін’єкція залежностей у диспетчер команд.      |
| `commands.file`              | фабрика, що повертає `<kernel.config_dir>/commands.php`   | Шлях до основного файлу опису команд.                         |
| `commands.directory`         | фабрика, що повертає `<kernel.config_dir>/commands`       | Директорія з додатковими файлами команд.                      |
| `commands.dispatcher`        | `CommandDispatcher::class`                                | Псевдонім для сервісу диспетчера команд.                      |

### Приклад конфігурації та використання

```php
// У Application::bootstrap():
$app->getRegistry()->set('kernel.configurators', [
    new Scaleum\Console\DependencyInjection\Commands(),
]);
$app->bootstrap();

// Отримання налаштувань з контейнера:
/** @var ContainerInterface $c */
$file = $c->get('commands.file');       // '/path/to/config/commands.php'
$dir  = $c->get('commands.directory');  // '/path/to/config/commands'

// Диспетчер команд
/** @var CommandDispatcher $dispatcher */
$dispatcher = $c->get('commands.dispatcher');
```

### Приклад структури файлу `commands.php`
```php
return [
    'greet'    => \Application\Commands\GreetCommand::class,
    'chat'     => \Application\Commands\ChatCommand::class,
    'migrate'  => \Application\Commands\MigrateCommand::class,
];
```
### Приклад використання
Файл `index.php` - точка входу в застосунок
```php
require __DIR__ . '/../vendor/autoload.php';
use Scaleum\Console\Application;

$app = new Application([
    'application_dir' => dirname(__DIR__, 1) . '/protected',
    'config_dir'      => dirname(__DIR__, 1) . '/protected/config',
    'environment'     => 'dev',
]);

$app->bootstrap();
$app->run();
```

### Виклик консольної команди
```
php .\index.php greet --name=User
```

[Назад](./application.md) | [Повернутися до змісту](../../index.md)