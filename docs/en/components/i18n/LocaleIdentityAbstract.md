[Back to Contents](../../index.md)

**EN** | [UK](../../../uk/components/i18n/LocaleIdentityAbstract.md) | [RU](../../../ru/components/i18n/LocaleIdentityAbstract.md)
#  LocaleIdentityAbstract

`LocaleIdentityAbstract` is a base abstract class for locale identifiers. It implements the `LocaleIdentityInterface` contract and extends `Hydrator`, providing lazy determination and caching of the locale string.

##  Properties

| Property | Type       | Access    | Purpose                                                                                           |
| -------- | --------- | --------- | ---------------------------------------------------------------------------------------------------- |
| `$name`  | `string \| null` | protected | Buffered locale name. Calculated once on the first call to `getName()`. |

##  Methods

| Signature              | Return Type         | Access | Purpose |
| -------------------- | ------------------------ | ------ | ---------- |
| `getName(): ?string` | `string \| null` | public     | Returns the locale. On first call, invokes `identify()` and caches the result. |                                                                                                                        |
| `identify(): bool \| string` *(abstract)* | `bool \| string`   | protected | Implemented in subclasses; should return the locale string or `false` if it cannot be determined.|

##  Inheritance Example

```php
<?php

declare(strict_types=1);

namespace App\I18n;

use Scaleum\i18n\LocaleIdentityAbstract;

class HeaderLocaleIdentity extends LocaleIdentityAbstract
{
    /**
     * Attempts to determine the locale from the Accept-Language header.
     */
    protected function identify(): bool|string
    {
        $accept = $_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? '';
        if ($accept === '') {
            return false; // locale not determined
        }

        // Take the first specified locale, e.g. "ru-RU,ru;q=0.9,en-US;q=0.8"
        [$raw] = explode(',', $accept);
        return str_replace('-', '_', trim($raw)); // ru_RU
    }
}
```

##  Usage Example

```php
<?php

use App\I18n\HeaderLocaleIdentity;

$localeIdentity = new HeaderLocaleIdentity(); // variable in lowerCamelCase

$currentLocale  = $localeIdentity->getName();

echo $currentLocale; // For example: ru_RU
```

##  Practical Tips

* The `identify()` method can determine the locale from **HTTP headers**, **configuration files**, **user session**, or other sources.
* If the locale cannot be determined, return `false` — this allows the calling code to handle the situation appropriately (e.g., select a default locale).
* Store the locale identifier in the `ll_CC` format (e.g., `en_US`) for compatibility with other i18n components.

[Back to Contents](../../index.md)
