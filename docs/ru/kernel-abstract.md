[Вернуться к оглавлению](./index.md)
# KernelAbstract

`KernelAbstract` — базовый абстрактный класс фреймворка Scaleum, управляющий процессом подготовки (bootstrap) и запуска приложения.

## Назначение

- Загрузка пользовательской конфигурации
- Загрузка конфигурационных файлов
- Установка конфигураторов контейнера
- Регистрация поведений (Behaviors)
- Регистрация сервисов
- Генерация события `KernelEvents::BOOTSTRAP`
- Генерация событий жизненного цикла `KernelEvents::START`, `KernelEvents::FINISH`, `KernelEvents::HALT`
- Перевод ядра в состояние готовности (`inReadiness = true`)


## Жизненный цикл

1. Объединение пользовательских параметров с реестром (`Registry`).
2. Загрузка файлов конфигурации (`kernel.configs`).
3. Регистрация конфигураторов контейнера.
4. Регистрация поведений (`behaviors`) через [EventManager](./components/events.md).
5. Регистрация сервисов (`services`) в [ServiceManager](./components/service-locator.md).
6. Генерация события `KernelEvents::BOOTSTRAP`.
7. Установка флага готовности (`inReadiness = true`).
8. Генерация события `KernelEvents::START` (однократно).
9. Выполнение основного обработчика (`HandlerInterface::handle()`) и отправка ответа.
10. Перед завершением процесса генерируется событие `KernelEvents::FINISH` (однократно).
11. Генерация события `KernelEvents::HALT` и завершение процесса (`exit`).

`KernelEvents::HALT` является последним событием жизненного цикла.

`run()` не перехватывает исключения: при ошибке управление передается выше по стеку.
Событие `KernelEvents::FINISH` генерируется один раз перед `KernelEvents::HALT`:
на успешном пути в `run()`, либо в `halt($code)` при аварийном завершении.

## Основные методы

| Метод | Назначение |
|:------|:-----------|
`getApplicationDir(): string` | Возвращает путь к папке проекты/приложения
`getConfigDir(): string` | Возвращает путь к папке конфигурации
`getEnvironment(): string` | Возвращает значения переменной окружения
`getEventManager(): EventManagerInterface` | Возвращает экземпляр [EventManager](./components/events.md)
`getServiceManager(): ServiceProviderInterface` | Возвращает экземпляр [ServiceManager](./components/service-locator.md)
`getContainer(): ContainerInterface` | Возвращает экземпляр контейнера [Container](./components/dependency-injection.md)
`bootstrap(array $config = []): self` | Запуск подготовки ядра: конфигурации, сервисы, события.
`run(): void` | Запуск основного потока: обработка запроса, отправка ответа; при успешном выполнении вызывает `FINISH` и `halt(0)`.
`isStarted(): bool` | Возвращает `true`, если событие `KernelEvents::START` уже было сгенерировано
`isFinished(): bool` | Возвращает `true`, если событие `KernelEvents::FINISH` уже было сгенерировано
`halt(int $code = 0): void` | Прерывает выполнение потока, при необходимости генерирует `FINISH` и устанавливает код (`$code`) завершения


## Пример использования

```php
require __DIR__ . '/../vendor/autoload.php';
use Scaleum\Core\KernelAbstract;

class MyApplication extends KernelAbstract
{
    public function run(): void
    {
        echo "Application is running.\n";
        parent::run();
    }
}

$app = new MyApplication([
    'application_dir' => dirname(__DIR__, 1) . '/protected',
    'config_dir'      => dirname(__DIR__, 1) . '/protected/config',
    'environment'     => 'dev',
]);

$app->bootstrap();
$app->run();
```

[Вернуться к оглавлению](./index.md)