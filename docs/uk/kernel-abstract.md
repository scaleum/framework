[Вернутися до змісту](./index.md)

[EN](../en/kernel-abstract.md) | **UK** | [RU](../ru/kernel-abstract.md)
# KernelAbstract

`KernelAbstract` — базовий абстрактний клас фреймворку Scaleum, який керує процесом підготовки (bootstrap) та запуску застосунку.

## Призначення

- Завантаження користувацької конфігурації
- Завантаження конфігураційних файлів
- Встановлення конфігураторів контейнера
- Реєстрація поведінок (Behaviors)
- Реєстрація сервісів
- Генерація події `KernelEvents::BOOTSTRAP`
- Генерація подій життєвого циклу `KernelEvents::START`, `KernelEvents::FINISH`, `KernelEvents::HALT`
- Переведення ядра у стан готовності (`inReadiness = true`)


## Життєвий цикл

1. Об’єднання користувацьких параметрів з реєстром (`Registry`).
2. Завантаження файлів конфігурації (`kernel.configs`).
3. Реєстрація конфігураторів контейнера.
4. Реєстрація поведінок (`behaviors`) через [EventManager](./components/events.md).
5. Реєстрація сервісів (`services`) у [ServiceManager](./components/service-locator.md).
6. Генерація події `KernelEvents::BOOTSTRAP`.
7. Встановлення прапорця готовності (`inReadiness = true`).
8. Генерація події `KernelEvents::START` (одноразово).
9. Виконання основного обробника (`HandlerInterface::handle()`) та відправка відповіді.
10. Перед завершенням процесу генерується подія `KernelEvents::FINISH` (одноразово).
11. Генерація події `KernelEvents::HALT` та завершення процесу (`exit`).

`KernelEvents::HALT` є останньою подією життєвого циклу.

`run()` не перехоплює виключення: при помилці керування передається вище по стеку.
Подія `KernelEvents::FINISH` генерується один раз перед `KernelEvents::HALT`:
на успішному шляху в `run()`, або в `halt($code)` при аварійному завершенні.

## Основні методи

| Метод | Призначення |
|:------|:------------|
`getApplicationDir(): string` | Повертає шлях до папки проєкту/застосунку
`getConfigDir(): string` | Повертає шлях до папки конфігурації
`getEnvironment(): string` | Повертає значення змінної оточення
`getEventManager(): EventManagerInterface` | Повертає екземпляр [EventManager](./components/events.md)
`getServiceManager(): ServiceProviderInterface` | Повертає екземпляр [ServiceManager](./components/service-locator.md)
`getContainer(): ContainerInterface` | Повертає екземпляр контейнера [Container](./components/dependency-injection.md)
`bootstrap(array $config = []): self` | Запуск підготовки ядра: конфігурації, сервіси, події.
`run(): void` | Запуск основного потоку: обробка запиту, відправка відповіді; при успішному виконанні викликає `FINISH` та `halt(0)`.
`isStarted(): bool` | Повертає `true`, якщо подія `KernelEvents::START` вже була згенерована
`isFinished(): bool` | Повертає `true`, якщо подія `KernelEvents::FINISH` вже була згенерована
`halt(int $code = 0): void` | Перериває виконання потоку, за потреби генерує `FINISH` та встановлює код (`$code`) завершення


## Приклад використання

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

[Вернутися до змісту](./index.md)