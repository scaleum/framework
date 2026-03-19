[Back to Contents](../index.md)

**EN** | [UK](../../uk/components/events.md) | [RU](../../ru/components/events.md)
#  Event Manager

The `EventManager` component of the Scaleum framework is an event management system implementing the `EventManagerInterface`.

##  Purpose

- Registration of events and subscribers
- Dispatching (sending) events
- Managing processing priorities
- Removing event listeners
- Support for stopping event processing
- Support for universal listeners (`*`)

##  Main Features

- Multiple subscriptions to one or several events
- Priority sorting of listeners
- Removal of registered listeners
- Dispatching events with parameters
- Automatic removal of one-off listeners
- Ability to stop further event propagation (`fireStopped()`)

##  Event Registration

####  Subscribing to an event

```php
$eventManager->on('user.registered', function (Event $event) {
    // event handling
});
```

####  Subscribing to multiple events at once
```php
$eventManager->on(['user.registered', 'user.activated'], function (Event $event) {
    // handling multiple events
});
```

####  Specifying subscriber priority
```php
$eventManager->on('order.paid', function (Event $event) {
    // handling with priority
}, priority: 10);
```

##  Retrieving the list of events and listeners
####  Get the list of all events
```php
$events = $eventManager->getEvents();
```

####  Get all listeners of a specific event
```php
$listeners = $eventManager->getListeners('user.registered');
```
Listeners will be sorted by priority (from lower to higher value).

##  Removing listeners
```php
$listener = $eventManager->on('user.banned', function ($event) {});

$eventManager->remove($listener);
```

##  Event Dispatching
####  Sending a simple event
```php
$eventManager->dispatch('user.registered', $context = null, $params = []);
```
- `context` — the execution context of the event (can be an object, string, etc.)
- `params` — an array of additional event parameters, accessible during event handling via `$event->getParam(string $name, mixed $default = null)` or `$event->getParams();`

####  Sending an event object
```php
$event = new Event([
    'name' => 'user.updated',
    'context' => $user,
    'params' => ['changes' => $changes],
]);

$eventManager->dispatch($event);
```

###  Dispatching behavior

- Each listener's callback function is called.
- If a listener returns a result, it is accumulated in the final array.
- If a callback filter for results is set, execution may be stopped.
- If the event sets the fireStopped() flag, event propagation stops.
- Listeners marked as one-off are automatically removed after processing.

###  Full cycle example
```php
$eventManager->on('user.created', function ($event) {
    echo 'User created: ' . $event->getParam('username','Unknown');
});

// somewhere in the code:
$eventManager->dispatch('user.created', null, ['username' => 'John']);
```
Result:  
```
User created: John
```

##  Key `EventManager` Methods
| Method | Purpose |
|:------|:-----------|
| on(string \| Event \| array $event, mixed $callback = null, int $priority = 1): array \| Listener | Register event handling |
| dispatch(string \| Event $event, mixed $context = null, array $params = [], callable $callback = null): array | Invoke event handling |
| getEvents(): array | Get the list of all events |
| getListeners(string $event): array | Get the list of event listeners |
| remove(Listener $listener): bool | Remove a listener |

[Back to Contents](../index.md)