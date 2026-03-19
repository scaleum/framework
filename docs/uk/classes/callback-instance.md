[Повернутись до змісту](../index.md)

[EN](../../en/classes/callback-instance.md) | **UK** | [RU](../../ru/classes/callback-instance.md)
# CallbackInstance

**CallbackInstance** — обгортка для універсальної роботи з callable та викликаними структурами.

## Призначення

- Інкапсуляція об'єкта callable (function, method, closure)
- Підтримка виклику через масив ['class' => ..., 'method' => ...]
- Збереження додаткових параметрів для виклику

## Основні можливості

| Метод | Призначення |
|:------|:------------|
| `__construct($callback)` | Конструктор, приймає callable або масив з class/method |
| `getCallback()` | Отримання кінцевого callable |
| `getParams()` | Отримання масиву параметрів |
| `setParams(array $params)` | Встановлення нових параметрів |

## Приклади використання

### Ініціалізація від анонімної функції

```php
$instance = new CallbackInstance(function() {
    return 'Hello';
});

$callback = $instance->getCallback();
echo $callback(); // Hello
```

### Ініціалізація від ['class' => ..., 'method' => ...]

```php
$instance = new CallbackInstance([
    'class' => SomeClass::class,
    'method' => 'someMethod',
]);

[$object, $method] = $instance->getCallback();
echo $object->$method();
```

## Винятки

- `EObjectError` — якщо некоректно зібрано масив callable.

[Повернутись до змісту](../index.md)