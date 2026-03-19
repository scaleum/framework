[Back to Contents](../../index.md)

**EN** | [UK](../../../uk/components/i18n/LocaleDetector.md) | [RU](../../../ru/components/i18n/LocaleDetector.md)
#  LocaleDetector

`LocaleDetector` inherits from `LocaleIdentityAbstract` and automatically determines the current application locale. It relies on environment variables (`LANG`, `LC_ALL`, `LC_CTYPE`), the `Accept-Language` header (when operating in a web environment), and the system locale. If necessary, it normalizes the string to the `ll_CC` format and adds a default country.

##  Properties

| Property            | Type                    | Access         | Purpose                          |
| ------------------- | ---------------------- | -------------- | ----------------------------------- |
| `$defaultCountries` | `array<string,string>` | private static | Mapping of ISO‑639‑1 language code to ISO‑3166‑1 default country code (e.g., `'ru' => 'RU'`). Used in `normalizeLocale()` to supplement the missing "country" part.|

##  Methods

| Signature | Return Type | Access         | Purpose |
|---|---|---|---|
| `identify(): bool\| string`| `bool\| string` | protected | Determines the locale in the following order: environment variables → `Accept-Language` header → `setlocale(LC_ALL, "0")`. Returns `false` if the locale could not be obtained.|
| `getName(): ?string` *(inherited)*          | `string \| null\`         | public | Returns the cached locale; on the first call, it invokes `identify()`.|
| `normalizeLocale(string $locale): string`     | `string`         | private static | Converts the string to the `ll_CC` format: removes encoding (`.UTF‑8`, etc.), adds the default country, adjusts character case.|
| `getDefaultCountry(string $language): string` | `string`         | private static | Returns the default country from `$defaultCountries` or `'US'` if the language is unknown.|

##  Usage Example

```php
<?php

declare(strict_types=1);

use Scaleum\i18n\LocaleDetector;

$localeDetector = new LocaleDetector(); // переменная lowerCamelCase

$currentLocale  = $localeDetector->getName(); // Например: ru_RU

echo $currentLocale;
```
> Changing the static property affects all `LocaleDetector` instances created after the change.

[Back to Contents](../../index.md)
