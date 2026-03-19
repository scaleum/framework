[Назад](./application.md) | [Повернутися до змісту](../../index.md)

[EN](../../../en/components/console/command-handler.md) | **UK** | [RU](../../../ru/components/console/command-handler.md)
# CommandHandler

`CommandHandler` — клас для обробки консольних запитів, що реалізує `HandlerInterface`. Відповідає за завантаження описів команд, реєстрацію їх у `CommandDispatcher` та виконання обраної команди.

## Призначення

- Завантаження конфігурацій команд з файлу та директорії.
- Реєстрація команд у `CommandDispatcher`.
- Генерація подій до та після обробки (`HandlerInterface::EVENT_GET_REQUEST`, `HandlerInterface::EVENT_GET_RESPONSE`).
- Виклик розподілу команд (`CommandDispatcher::dispatch`).

## Конструктор

```php
public function __construct(ContainerInterface $container)
```
- З контейнера PSR-11 отримує сервіс подій за псевдонімом `Framework::SVC_EVENTS`.
- Перевіряє, що сервіс реалізує `EventManagerInterface`, інакше кидає `ERuntimeError`.

## Метод handle()

```php
public function handle(): ResponderInterface
```
1. Отримання диспетчера
   ```php
   $dispatcher = $this->container->get('commands.dispatcher');
   ```
2. Завантаження описів команд
   ```php
   $loader   = $this->container->get(LoaderResolver::class);
   $commands = [];
   if (file_exists($file = $container->get('commands.file'))) {
       $commands = $loader->fromFile($file);
   }
   if (is_dir($dir = $container->get('commands.directory'))) {
       $commands = ArrayHelper::merge($commands, $loader->fromDir($dir));
   }
   ```
3. Реєстрація команд
   ```php
   foreach ($commands as $name => $class) {
       $dispatcher->registerCommand($name, $container->get($class));
   }
   ```
4. Генерація події перед виконанням
   ```php
   $this->events->dispatch(
       HandlerInterface::EVENT_GET_REQUEST,
       $this,
       ['request' => new Request()]
   );
   ```
5. Виклик `CommandDispatcher::dispatch`
   ```php
   $response = $dispatcher->dispatch(new Request());
   ```
6. Генерація події після виконання
   ```php
   $this->events->dispatch(
       HandlerInterface::EVENT_GET_RESPONSE,
       $this,
       ['response' => $response]
   );
   ```
7. Повернення `ResponderInterface`
   ```php
   return $response;
   ```

### Приклад використання

```php
use Scaleum\Console\Application;

$app = new Application([...]);
$app->bootstrap();
/ @var ResponderInterface $response */
$response = $app->getHandler()->handle();
// $response->send() або exit($response->getExitCode());
```
[Назад](./application.md) | [Повернутися до змісту](../../index.md)