[Вернуться к оглавлению](../index.md)

# CallbackInstance

**CallbackInstance** — обертка для универсальной работы с callable и вызываемыми структурами.

## Назначение

- Инкапсуляция объекта callable (function, method, closure)
- Поддержка вызова через массив ['class' => ..., 'method' => ...]
- Хранение дополнительных параметров для вызова

## Основные возможности

| Метод | Назначение |
|:------|:-----------|
| `__construct($callback)` | Конструктор, принимает callable или массив с class/method |
| `getCallback()` | Получение конечного callable |
| `getParams()` | Получение массива параметров |
| `setParams(array $params)` | Установка новых параметров |

## Примеры использования

### Инициализация от анонимной функции

```php
$instance = new CallbackInstance(function() {
    return 'Hello';
});

$callback = $instance->getCallback();
echo $callback(); // Hello
```

### Инициализация от ['class' => ..., 'method' => ...]

```php
$instance = new CallbackInstance([
    'class' => SomeClass::class,
    'method' => 'someMethod',
]);

[$object, $method] = $instance->getCallback();
echo $object->$method();
```

## Исключения

- `EObjectError` — если некорректно собран массив callable.

[Вернуться к оглавлению](../index.md)
