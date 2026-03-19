[Back to Contents](../index.md)

**EN** | [UK](../../uk/classes/attribute-container.md) | [RU](../../ru/classes/attribute-container.md)
#  AttributeContainerInterface

`AttributeContainerInterface` — a contract for classes that support storing and managing attributes.

##  Method Descriptions

| Method | Purpose |
|:------|:--------|
| `getAttribute(string $key, mixed $default = null): mixed` | Retrieve the value of an attribute by key |
| `hasAttribute(string $key): bool` | Check if an attribute exists |
| `setAttribute(string $key, mixed $value = null): static` | Set the value of an attribute |
| `deleteAttribute(string $key): static` | Delete an attribute |
| `getAttributes(): array` | Retrieve all attributes |
| `setAttributes(array $value): static` | Replace all attributes |

#  AttributeContainer

`AttributeContainer` — implementation of `AttributeContainerInterface`, stores attributes in an array.

##  Description

- Access via magic methods `__get` and `__set`
- Automatic merging of arrays when using setAttribute
- Ability to explicitly replace attributes

##  Usage Examples

###  Setting and reading an attribute

```php
$container = new AttributeContainer();
$container->setAttribute('user_id', 42);
echo $container->getAttribute('user_id'); // 42
```

###  Deleting an attribute

```php
$container->deleteAttribute('user_id');
```

###  Merging arrays

```php
$container->setAttribute('settings', ['theme' => 'dark']);
$container->setAttribute('settings', ['language' => 'en']);
print_r($container->getAttribute('settings'));
// ['theme' => 'dark', 'language' => 'en']
```

###  Replacing all attributes

```php
$container->setAttributes(['new' => 'value']);
```
[Back to Contents](../index.md)