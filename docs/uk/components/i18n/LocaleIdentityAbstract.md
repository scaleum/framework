[Повернутись до змісту](../../index.md)

[EN](../../../en/components/i18n/LocaleIdentityAbstract.md) | **UK** | [RU](../../../ru/components/i18n/LocaleIdentityAbstract.md)
# LocaleIdentityAbstract

`LocaleIdentityAbstract` — базовий абстрактний клас-ідентифікатор локалі. Реалізує контракт `LocaleIdentityInterface` і наслідується від `Hydrator`, надаючи ліниве визначення та кешування рядка локалі.

## Властивості

| Властивість | Тип       | Доступ    | Призначення                                                                                           |
| ----------- | --------- | --------- | ---------------------------------------------------------------------------------------------------- |
| `$name`     | `string \| null` | protected | Буферизоване ім'я локалі. Обчислюється один раз при першому виклику `getName()`. |

## Методи

| Підпис               | Повертаємий тип         | Доступ | Призначення |
| -------------------- | ----------------------- | ------ | ----------- |
| `getName(): ?string` | `string \| null`        | public | Повертає локаль. При першому зверненні викликає `identify()` і кешує результат. |                                                                                                                        |
| `identify(): bool \| string` *(абстрактний)* | `bool \| string`   | protected | Реалізується в нащадках; має повернути рядок локалі або `false`, якщо визначити не вдалося.|

## Приклад наслідування

```php
<?php

declare(strict_types=1);

namespace App\I18n;

use Scaleum\i18n\LocaleIdentityAbstract;

class HeaderLocaleIdentity extends LocaleIdentityAbstract
{
    /**
     * Намагаємося визначити локаль із заголовка Accept-Language.
     */
    protected function identify(): bool|string
    {
        $accept = $_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? '';
        if ($accept === '') {
            return false; // локаль не визначена
        }

        // Беремо першу вказану локаль, наприклад "ru-RU,ru;q=0.9,en-US;q=0.8"
        [$raw] = explode(',', $accept);
        return str_replace('-', '_', trim($raw)); // ru_RU
    }
}
```

## Приклад використання

```php
<?php

use App\I18n\HeaderLocaleIdentity;

$localeIdentity = new HeaderLocaleIdentity(); // змінна lowerCamelCase

$currentLocale  = $localeIdentity->getName();

echo $currentLocale; // Наприклад: ru_RU
```

## Практичні поради

* Метод `identify()` може визначати локаль із **заголовків HTTP**, **конфігураційних файлів**, **сесії користувача** або іншого джерела.
* Якщо локаль не вдається визначити, повертайте `false` — це дозволить викликаючому коду обробити ситуацію по-своєму (наприклад, вибрати локаль за замовчуванням).
* Зберігайте ідентифікатор локалі у вигляді `ll_CC` (наприклад, `en_US`) для сумісності з іншими компонентами i18n.

[Повернутись до змісту](../../index.md)