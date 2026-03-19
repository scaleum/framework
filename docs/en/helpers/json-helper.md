[Back to Contents](../index.md)

**EN** | [UK](../../uk/helpers/json-helper.md) | [RU](../../ru/helpers/json-helper.md)
#  JsonHelper

`JsonHelper` is a utility class for working with JSON in the Scaleum Framework.

##  Constants

| Name | Value | Description |
|:----|:---------|:---------|
| `DEFAULT_JSON_FLAGS` | Combination of JSON flags | Standard set of JSON encoding flags: no escaping of slashes, no escaping of Unicode, preserving zeros after the decimal point, replacing invalid characters, partial output on error |

##  Main Methods

| Method | Purpose |
|:------|:-----------|
| `isJson(mixed $string)` | Checks if a string is valid JSON |
| `encode(mixed $data, ?int $encodeFlags = null)` | Encodes data into a JSON string |
| `decode(string $json, ?bool $associative = null, int $depth = 512, int $flags = 0)` | Decodes data from a JSON string |

##  Usage Examples

###  Checking JSON String Validity

```php
if (JsonHelper::isJson('{"key":"value"}')) {
    echo 'The string is valid JSON';
}
```

###  Encoding an Array to a JSON String

```php
$data = ['key' => 'value', 'number' => 123];
$json = JsonHelper::encode($data);
echo $json; // {"key":"value","number":123}
```

[Back to Contents](../index.md)