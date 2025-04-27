[Вернуться к оглавлению](../index.md)
# AttributeContainerInterface

`AttributeContainerInterface` — контракт для классов, поддерживающих хранение и управление атрибутами.

## Описание методов

| Метод | Назначение |
|:------|:-----------|
| `getAttribute(string $key, mixed $default = null): mixed` | Получение значения атрибута по ключу |
| `hasAttribute(string $key): bool` | Проверка существования атрибута |
| `setAttribute(string $key, mixed $value = null): static` | Установка значения атрибута |
| `deleteAttribute(string $key): static` | Удаление атрибута |
| `getAttributes(): array` | Получение всех атрибутов |
| `setAttributes(array $value): static` | Замена всех атрибутов |

# AttributeContainer

`AttributeContainer` — реализация `AttributeContainerInterface`, хранит атрибуты в массиве.

## Описание

- Доступ через magic-методы `__get` и `__set`
- Автоматическое слияние массивов при setAttribute
- Возможность явной замены атрибутов

## Примеры использования

### Установка и чтение атрибута

```php
$container = new AttributeContainer();
$container->setAttribute('user_id', 42);
echo $container->getAttribute('user_id'); // 42
```

### Удаление атрибута

```php
$container->deleteAttribute('user_id');
```

### Объединение массивов

```php
$container->setAttribute('settings', ['theme' => 'dark']);
$container->setAttribute('settings', ['language' => 'en']);
print_r($container->getAttribute('settings'));
// ['theme' => 'dark', 'language' => 'en']
```

### Замена всех атрибутов

```php
$container->setAttributes(['new' => 'value']);
```
[Вернуться к оглавлению](../index.md)