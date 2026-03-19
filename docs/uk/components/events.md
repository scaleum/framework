[Повернутись до змісту](../index.md)

[EN](../../en/components/events.md) | **UK** | [RU](../../ru/components/events.md)
# Event Manager

Компонент `EventManager` фреймворка Scaleum — це система управління подіями, що реалізує інтерфейс `EventManagerInterface`.

## Призначення

- Реєстрація подій та підписників
- Диспетчеризація (відправка) подій
- Управління пріоритетами обробки
- Видалення слухачів подій
- Підтримка зупинки обробки подій
- Підтримка універсальних слухачів (`*`)

## Основні можливості

- Множинна підписка на одну або кілька подій
- Пріоритетне сортування слухачів
- Видалення зареєстрованих слухачів
- Диспетчеризація подій з параметрами
- Автоматичне видалення одноразових (`one-off`) слухачів
- Можливість зупинити подальше поширення події (`fireStopped()`)


## Реєстрація подій

#### Підписка на подію

```php
$eventManager->on('user.registered', function (Event $event) {
    // обробка події
});
```

#### Підписка на кілька подій одразу
```php
$eventManager->on(['user.registered', 'user.activated'], function (Event $event) {
    // обробка кількох подій
});
```

#### Вказання пріоритету підписника
```php
$eventManager->on('order.paid', function (Event $event) {
    // обробка з пріоритетом
}, priority: 10);
```

## Отримання списку подій і слухачів
#### Отримати список усіх подій
```php
$events = $eventManager->getEvents();
```

#### Отримати всіх слухачів певної події
```php
$listeners = $eventManager->getListeners('user.registered');
```
Слухачі будуть відсортовані за пріоритетом (від меншого значення до більшого).

## Видалення слухачів
```php
$listener = $eventManager->on('user.banned', function ($event) {});

$eventManager->remove($listener);
```

## Диспетчеризація подій
#### Відправка простої події
```php
$eventManager->dispatch('user.registered', $context = null, $params = []);
```
- `context` — контекст виконання події (може бути об’єкт, рядок тощо)
- `params` — масив додаткових параметрів події, при обробці події доступні через `$event->getParam(string $name, mixed $default = nul)` або `$event->getParams();`;

#### Відправка об’єкта події
```php
$event = new Event([
    'name' => 'user.updated',
    'context' => $user,
    'params' => ['changes' => $changes],
]);

$eventManager->dispatch($event);
```

### Поведінка диспетчеризації

- Для кожного слухача викликається його callback-функція.
- Якщо слухач повернув результат — він накопичується у підсумковому масиві.
- Якщо задано callback-фільтр для результатів, виконання може бути зупинено.
- Якщо подія встановить прапорець fireStopped(), поширення події припиняється.
- Слухачі, позначені як одноразові (one-off), автоматично видаляються після обробки.

### Приклад повного циклу
```php
$eventManager->on('user.created', function ($event) {
    echo 'User created: ' . $event->getParam('username','Unknown');
});

// десь у коді:
$eventManager->dispatch('user.created', null, ['username' => 'John']);
```
Результат:  
```
User created: John
```

## Ключові методи `EventManager`
| Метод | Призначення |
|:------|:------------|
| on(string \| Event \| array $event, mixed $callback = null, int $priority = 1): array \| Listener | Реєстрація обробки події |
| dispatch(string \| Event $event, mixed $context = null, array $params = [], callable $callback = null): array | Виклик обробки події |
| getEvents(): array | Отримання списку усіх подій |
| getListeners(string $event): array | Отримання списку слухачів події |
| remove(Listener $listener): bool | Видалення слухача |

[Повернутись до змісту](../index.md)