[Повернутись до змісту](../index.md)

[EN](../../en/helpers/type-helper.md) | **UK** | [RU](../../ru/helpers/type-helper.md)
# TypeHelper

`TypeHelper` — утилітарний клас для визначення типів змінних та перевірки на відповідність типам у Scaleum Framework.

## Призначення

- Визначення типу значення
- Перевірка типу змінної

## Основні типи

| Константа | Опис |
|:------|:-----------|
| `TYPE_ARRAY` | Масив |
| `TYPE_BOOL` | Булевий тип |
| `TYPE_CALLABLE` | Викликаний тип |
| `TYPE_FLOAT` | Число з плаваючою комою |
| `TYPE_INT` | Ціле число |
| `TYPE_NULL` | null |
| `TYPE_NUMERIC` | Числовий тип |
| `TYPE_OBJECT` | Об'єкт |
| `TYPE_RESOURCE` | Ресурс |
| `TYPE_STRING` | Рядок |

## Основні методи

| Метод | Призначення |
|:------|:-----------|
| `getType($var)` | Визначення типу змінної |
| `isType($var, $type)` | Перевірка, чи відповідає тип |

## Приклади використання

### Визначення типу змінної

```php
$type = TypeHelper::getType(123); // int
```

### Перевірка типу на масив

```php
$isArray = TypeHelper::isType([1,2,3], TypeHelper::TYPE_ARRAY); // true
```

[Повернутись до змісту](../index.md)