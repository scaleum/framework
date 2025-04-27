[Вернуться к оглавлению](../index.md)
# XmlHelper

`XmlHelper` — утилитарный класс для работы с XML в Scaleum Framework.

## Назначение

- Проверка валидности XML-строки

## Основные методы

| Метод | Назначение |
|:------|:-----------|
| `isXml(string $str)` | Проверка, является ли строка валидным XML |

## Примеры использования

### Проверка валидности XML

```php
$isValidXml = XmlHelper::isXml('<note><to>User</to><from>Admin</from><body>Hello</body></note>');
```
[Вернуться к оглавлению](../index.md)