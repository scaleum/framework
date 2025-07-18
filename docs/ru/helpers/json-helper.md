[Вернуться к оглавлению](../index.md)
# JsonHelper

`JsonHelper` — утилитарный класс для работы с JSON в Scaleum Framework.

## Константы

| Имя | Значение | Описание |
|:----|:---------|:---------|
| `DEFAULT_JSON_FLAGS` | Комбинация флагов JSON | Стандартный набор флагов кодирования JSON: без экранирования слешей, без экранирования Unicode, сохранение нулей после точки, замена невалидных символов, частичный вывод при ошибке |

## Основные методы

| Метод | Назначение |
|:------|:-----------|
| `isJson(mixed $string)` | Проверка, является ли строка валидным JSON |
| `encode(mixed $data, ?int $encodeFlags = null)` | Кодирование данных в JSON строку |
| `decode(string $json, ?bool $associative = null, int $depth = 512, int $flags = 0)` | Декодирование данных из JSON строки |

## Примеры использования

### Проверка валидности JSON-строки

```php
if (JsonHelper::isJson('{"key":"value"}')) {
    echo 'Строка является валидным JSON';
}
```

### Кодирование массива в JSON строку

```php
$data = ['key' => 'value', 'number' => 123];
$json = JsonHelper::encode($data);
echo $json; // {"key":"value","number":123}
```

[Вернуться к оглавлению](../index.md)