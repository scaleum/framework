[Назад](./application.md) | [Вернуться к оглавлению](../../index.md)
# CommandHandler

`CommandHandler` — класс для обработки консольных запросов, реализующий `HandlerInterface`. Отвечает за загрузку описаний команд, регистрацию их в `CommandDispatcher` и выполнение выбранной команды.

## Назначение

- Загрузка конфигураций команд из файла и директории.
- Регистрация команд в `CommandDispatcher`.
- Генерация событий до и после обработки (`HandlerInterface::EVENT_GET_REQUEST`, `HandlerInterface::EVENT_GET_RESPONSE`).
- Вызов распределения команд (`CommandDispatcher::dispatch`).

## Конструктор

```php
public function __construct(ContainerInterface $container)
```
- Из контейнера PSR-11 получает сервис событий по псевдониму `Framework::SVC_EVENTS`.
- Проверяет, что сервис реализует `EventManagerInterface`, иначе бросает `ERuntimeError`.

## Метод handle()

```php
public function handle(): ResponderInterface
```
1. Получение диспетчера
   ```php
   $dispatcher = $this->container->get('commands.dispatcher');
   ```
2. Загрузка описаний команд
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
3. Регистрация команд
   ```php
   foreach ($commands as $name => $class) {
       $dispatcher->registerCommand($name, $container->get($class));
   }
   ```
4. Генерация события перед выполнением
   ```php
   $this->events->dispatch(
       HandlerInterface::EVENT_GET_REQUEST,
       $this,
       ['request' => new Request()]
   );
   ```
5. Вызов `CommandDispatcher::dispatch`
   ```php
   $response = $dispatcher->dispatch(new Request());
   ```
6. Генерация события после выполнения
   ```php
   $this->events->dispatch(
       HandlerInterface::EVENT_GET_RESPONSE,
       $this,
       ['response' => $response]
   );
   ```
7. Возврат `ResponderInterface`
   ```php
   return $response;
   ```

### Пример использования

```php
use Scaleum\Console\Application;

$app = new Application([...]);
$app->bootstrap();
/ @var ResponderInterface $response */
$response = $app->getHandler()->handle();
// $response->send() или exit($response->getExitCode());
```
[Назад](./application.md) | [Вернуться к оглавлению](../../index.md)

