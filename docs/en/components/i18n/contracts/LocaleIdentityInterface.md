#  LocaleIdentityInterface

**EN** | [UK](../../../../uk/components/i18n/contracts/LocaleIdentityInterface.md) | [RU](../../../../ru/components/i18n/contracts/LocaleIdentityInterface.md)
**Namespace:** `Scaleum\i18n\Contracts`

The interface describes the simplest locale identifier object. It is used in I18N components for passing and storing the locale string (for example, `ru_RU`, `en_US`). By extending or implementing this interface, you can integrate your own localization objects with the Scaleum infrastructure.

---

##  Methods

| Signature            | Return Type      | Purpose    |                                                                       |
| -------------------- | ---------------- | ---------- | --------------------------------------------------------------------- |
| `getName(): ?string` | \`string         | null\`     | Returns the symbolic name of the locale. If the locale is not defined — `null`. |

---

##  Implementation Example

```php
<?php

declare(strict_types=1);

namespace App\I18n;

use Scaleum\i18n\Contracts\LocaleIdentityInterface;

class LocaleIdentity implements LocaleIdentityInterface
{
    private string $name;

    public function __construct(string $name)
    {
        $this->name = $name;
    }

    public function getName(): ?string
    {
        return $this->name;
    }
}
```

---

##  Usage Example

```php
<?php

use App\I18n\LocaleIdentity;

$localeIdentity = new LocaleIdentity('ru_RU'); // variable lowerCamelCase

// Outputs: ru_RU
echo $localeIdentity->getName();
```

---

##  Integration Recommendations

* **Store in place**: pass `LocaleIdentityInterface` into service constructors where current locale information is required.
* **Value object**: avoid changing state after creation — the locale identifier should be immutable.
