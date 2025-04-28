[Назад](./application.md) | [Вернуться к оглавлению](../../index.md)
# Commands

Класс `Scaleum\Console\DependencyInjection\Commands` — конфигуратор контейнера зависимостей для модуля консольных команд. Регистрирует сервисы и параметры, необходимые для поиска и запуска CLI-команд.

## Назначение

- Определить в контейнере сервисы и настройки для консольного режима.
- Настроить диспетчер команд (`CommandDispatcher`).
- Указать пути к файлам и директориям с описанием команд.

## Связь с ядром

Используется в `Console\Application::bootstrap()`, где добавляется в реестр конфигураторов ядра:
```php
$this->getRegistry()->set('kernel.configurators', [
    new Commands(),
]);
```  
Таким образом при `KernelAbstract::bootstrap()` вызывается `Commands::configure()`, и контейнер получает определения для команды CLI.

## Основные задачи

- Регистрация класса `CommandDispatcher` для маршрутизации и выполнения команд.
- Внедрение параметров:
  - `commands.file`  — путь к файлу `commands.php` в директории конфига ядра.
  - `commands.directory` — директория с дополнительными файлами команд.
  - `commands.dispatcher` — псевдоним сервиса диспетчера команд.

## Определения в контейнере

| Имя в контейнере               | Значение / Сервис                                          | Описание                                                      |
|:-------------------------------|:-----------------------------------------------------------|:--------------------------------------------------------------|
| `CommandDispatcher::class`     | `Autowire::create()`                                       | Автоматическая автоинъекция зависимостей в диспетчер команд.  |
| `commands.file`                | фабрика, возвращающая `<kernel.config_dir>/commands.php`   | Путь к основному файлу описания команд.                       |
| `commands.directory`           | фабрика, возвращающая `<kernel.config_dir>/commands`       | Директория с дополнительными файлами команд.                  |
| `commands.dispatcher`          | `CommandDispatcher::class`                                 | Псевдоним для сервиса диспетчера команд.                     |

### Пример конфигурации и использования

```php
// В Application::bootstrap():
$app->getRegistry()->set('kernel.configurators', [
    new Scaleum\Console\DependencyInjection\Commands(),
]);
$app->bootstrap();

// Получение настроек из контейнера:
/** @var ContainerInterface $c */
$file = $c->get('commands.file');       // '/path/to/config/commands.php'
$dir  = $c->get('commands.directory');  // '/path/to/config/commands'

// Диспетчер команд
/** @var CommandDispatcher $dispatcher */
$dispatcher = $c->get('commands.dispatcher');
```

### Пример структуры файла `commands.php`
```php
return [
    'greet'    => \Application\Commands\GreetCommand::class,
    'chat'     => \Application\Commands\ChatCommand::class,
    'migrate'  => \Application\Commands\MigrateCommand::class,
];
```
### Пример использования
Файл `index.php` - точка входа в приложение
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

### Вызов консольной команды
```
php .\index.php greet --name=User
```

[Назад](./application.md) | [Вернуться к оглавлению](../../index.md)

