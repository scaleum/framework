# LocaleIdentityInterface

**Пространство имён:** `Scaleum\i18n\Contracts`

Интерфейс описывает простейший объект‑идентификатор локали.  Используется в компонентах I18N для передачи и хранения строки локали (например, `ru_RU`, `en_US`).  Расширяя или реализуя данный интерфейс можно интегрировать собственные объекты локализации с инфраструктурой Scaleum.

---

## Методы

| Подпись              | Возвращаемый тип | Назначение |                                                                       |
| -------------------- | ---------------- | ---------- | --------------------------------------------------------------------- |
| `getName(): ?string` | \`string         | null\`     | Возвращает символьное имя локали. Если локаль не определена — `null`. |

---

## Пример реализации

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

## Пример использования

```php
<?php

use App\I18n\LocaleIdentity;

$localeIdentity = new LocaleIdentity('ru_RU'); // переменная lowerCamelCase

// Выведет: ru_RU
echo $localeIdentity->getName();
```

---

## Рекомендации по интеграции

* **Хранение по месту**: передавайте `LocaleIdentityInterface` в конструкторы сервисов, где требуется информация о текущей локали.
* **Объект‑значение**: избегайте изменения состояния после создания — идентификатор локали должен быть неизменяемым (immutable).
