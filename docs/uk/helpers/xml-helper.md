[Вернутися до змісту](../index.md)

[EN](../../en/helpers/xml-helper.md) | **UK** | [RU](../../ru/helpers/xml-helper.md)
# XmlHelper

`XmlHelper` — утилітарний клас для роботи з XML у Scaleum Framework.

## Призначення

- Перевірка валідності XML-рядка

## Основні методи

| Метод | Призначення |
|:------|:------------|
| `isXml(string $str)` | Перевірка, чи є рядок валідним XML |

## Приклади використання

### Перевірка валідності XML

```php
$isValidXml = XmlHelper::isXml('<note><to>User</to><from>Admin</from><body>Hello</body></note>');
```
[Вернутися до змісту](../index.md)