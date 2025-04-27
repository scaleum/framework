[Вернуться к оглавлению](../index.md)
# Event Manager

Компонент `EventManager` фреймворка Scaleum — это система управления событиями, реализующая интерфейс `EventManagerInterface`.

## Назначение

- Регистрация событий и подписчиков
- Диспетчеризация (отправка) событий
- Управление приоритетами обработки
- Удаление слушателей событий
- Поддержка остановки обработки событий
- Поддержка универсальных слушателей (`*`)

## Основные возможности

- Множественная подписка на одно или несколько событий
- Приоритетная сортировка слушателей
- Удаление зарегистрированных слушателей
- Диспетчеризация событий с параметрами
- Автоматическое удаление одноразовых (`one-off`) слушателей
- Возможность остановить дальнейшее распространение события (`fireStopped()`)


## Регистрация событий

#### Подписка на событие

```php
$eventManager->on('user.registered', function (Event $event) {
    // обработка события
});
```

#### Подписка на несколько событий сразу
```php
$eventManager->on(['user.registered', 'user.activated'], function (Event $event) {
    // обработка нескольких событий
});
```

#### Указание приоритета подписчика
```php
$eventManager->on('order.paid', function (Event $event) {
    // обработка с приоритетом
}, priority: 10);
```

## Получение списка событий и слушателей
#### Получить список всех событий
```php
$events = $eventManager->getEvents();
```

#### Получить всех слушателей определённого события
```php
$listeners = $eventManager->getListeners('user.registered');
```
Слушатели будут отсортированы по приоритету (от меньшего значения к большему).

## Удаление слушателей
```php
$listener = $eventManager->on('user.banned', function ($event) {});

$eventManager->remove($listener);
```

## Диспетчеризация событий
#### Отправка простого события
```php
$eventManager->dispatch('user.registered', $context = null, $params = []);
```
- `context` — контекст выполнения события (может быть объект, строка и т.д.)
- `params` — массив дополнительных параметров события, при обработке события доступны через `$event->getParam(string $name, mixed $default = nul)` или `$event->getParams();`;

#### Отправка объекта события
```php
$event = new Event([
    'name' => 'user.updated',
    'context' => $user,
    'params' => ['changes' => $changes],
]);

$eventManager->dispatch($event);
```

### Поведение диспетчеризации

- Для каждого слушателя вызывается его callback-функция.
- Если слушатель вернул результат — он накапливается в итоговом массиве.
- Если задан callback-фильтр для результатов, выполнение может быть остановлено.
- Если событие установит флаг fireStopped(), распространение события прекращается.
- Слушатели, отмеченные как одноразовые (one-off), автоматически удаляются после обработки.

### Пример полного цикла
```php
$eventManager->on('user.created', function ($event) {
    echo 'User created: ' . $event->getParam('username','Unknown');
});

// где-то в коде:
$eventManager->dispatch('user.created', null, ['username' => 'John']);
```
Результат:  
```
User created: John
```

## Ключевые методы `EventManager`
| Метод | Назначение |
|:------|:-----------|
| on(string \| Event \| array $event, mixed $callback = null, int $priority = 1): array \| Listener | Регистрация обработки события |
| dispatch(string \| Event $event, mixed $context = null, array $params = [], callable $callback = null): array | Вызов обработки события |
| getEvents(): array | Получение списка всех событий |
| getListeners(string $event): array | Получение списка слушателей события |
| remove(Listener $listener): bool | Удаление слушателя |

[Вернуться к оглавлению](../index.md)