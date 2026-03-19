[Повернутись до змісту](../index.md)

[EN](../../en/helpers/json-helper.md) | **UK** | [RU](../../ru/helpers/json-helper.md)
# JsonHelper

`JsonHelper` — утилітарний клас для роботи з JSON у Scaleum Framework.

## Константи

| Ім'я | Значення | Опис |
|:----|:---------|:---------|
| `DEFAULT_JSON_FLAGS` | Комбінація прапорців JSON | Стандартний набір прапорців кодування JSON: без екранування слешів, без екранування Unicode, збереження нулів після крапки, заміна невалідних символів, частковий вивід при помилці |

## Основні методи

| Метод | Призначення |
|:------|:-----------|
| `isJson(mixed $string)` | Перевірка, чи є рядок валідним JSON |
| `encode(mixed $data, ?int $encodeFlags = null)` | Кодування даних у JSON рядок |
| `decode(string $json, ?bool $associative = null, int $depth = 512, int $flags = 0)` | Декодування даних з JSON рядка |

## Приклади використання

### Перевірка валідності JSON-рядка

```php
if (JsonHelper::isJson('{"key":"value"}')) {
    echo 'Рядок є валідним JSON';
}
```

### Кодування масиву у JSON рядок

```php
$data = ['key' => 'value', 'number' => 123];
$json = JsonHelper::encode($data);
echo $json; // {"key":"value","number":123}
```

[Повернутись до змісту](../index.md)