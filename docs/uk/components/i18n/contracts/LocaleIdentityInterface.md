# LocaleIdentityInterface

[EN](../../../../en/components/i18n/contracts/LocaleIdentityInterface.md) | **UK** | [RU](../../../../ru/components/i18n/contracts/LocaleIdentityInterface.md)
**Простір імен:** `Scaleum\i18n\Contracts`

Інтерфейс описує найпростіший об'єкт‑ідентифікатор локалі. Використовується в компонентах I18N для передачі та зберігання рядка локалі (наприклад, `ru_RU`, `en_US`). Розширюючи або реалізуючи цей інтерфейс, можна інтегрувати власні об'єкти локалізації з інфраструктурою Scaleum.

---

## Методи

| Підпис               | Повертаємий тип  | Призначення |                                                                       |
| -------------------- | ---------------- | ----------- | --------------------------------------------------------------------- |
| `getName(): ?string` | \`string         | null\`     | Повертає символьне ім'я локалі. Якщо локаль не визначена — `null`.    |

---

## Приклад реалізації

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

## Приклад використання

```php
<?php

use App\I18n\LocaleIdentity;

$localeIdentity = new LocaleIdentity('ru_RU'); // змінна lowerCamelCase

// Виведе: ru_RU
echo $localeIdentity->getName();
```

---

## Рекомендації щодо інтеграції

* **Зберігання на місці**: передавайте `LocaleIdentityInterface` у конструктори сервісів, де потрібна інформація про поточну локаль.
* **Об'єкт‑значення**: уникайте зміни стану після створення — ідентифікатор локалі має бути незмінним (immutable).