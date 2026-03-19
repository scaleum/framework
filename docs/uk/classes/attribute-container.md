[Повернутись до змісту](../index.md)

[EN](../../en/classes/attribute-container.md) | **UK** | [RU](../../ru/classes/attribute-container.md)
# AttributeContainerInterface

`AttributeContainerInterface` — контракт для класів, що підтримують зберігання та керування атрибутами.

## Опис методів

| Метод | Призначення |
|:------|:------------|
| `getAttribute(string $key, mixed $default = null): mixed` | Отримання значення атрибута за ключем |
| `hasAttribute(string $key): bool` | Перевірка існування атрибута |
| `setAttribute(string $key, mixed $value = null): static` | Встановлення значення атрибута |
| `deleteAttribute(string $key): static` | Видалення атрибута |
| `getAttributes(): array` | Отримання всіх атрибутів |
| `setAttributes(array $value): static` | Замінa всіх атрибутів |

# AttributeContainer

`AttributeContainer` — реалізація `AttributeContainerInterface`, зберігає атрибути у масиві.

## Опис

- Доступ через magic-методи `__get` та `__set`
- Автоматичне злиття масивів при setAttribute
- Можливість явної заміни атрибутів

## Приклади використання

### Встановлення та читання атрибута

```php
$container = new AttributeContainer();
$container->setAttribute('user_id', 42);
echo $container->getAttribute('user_id'); // 42
```

### Видалення атрибута

```php
$container->deleteAttribute('user_id');
```

### Об’єднання масивів

```php
$container->setAttribute('settings', ['theme' => 'dark']);
$container->setAttribute('settings', ['language' => 'en']);
print_r($container->getAttribute('settings'));
// ['theme' => 'dark', 'language' => 'en']
```

### Заміна всіх атрибутів

```php
$container->setAttributes(['new' => 'value']);
```
[Повернутись до змісту](../index.md)