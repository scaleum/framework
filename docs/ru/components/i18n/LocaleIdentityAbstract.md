[Вернуться к оглавлению](../../index.md)

# LocaleIdentityAbstract

`LocaleIdentityAbstract` — базовый абстрактный класс‑идентификатор локали. Реализует контракт `LocaleIdentityInterface` и наследуется от `Hydrator`, предоставляя ленивое определение и кэширование строки локали.

## Свойства

| Свойство | Тип       | Доступ    | Назначение                                                                                           |
| -------- | --------- | --------- | ---------------------------------------------------------------------------------------------------- |
| `$name`  | `string \| null` | protected | Буферизованное имя локали. Вычисляется один раз при первом вызове `getName()`. |

## Методы

| Подпись              | Возвращаемый тип         | Доступ | Назначение |
| -------------------- | ------------------------ | ------ | ---------- |
| `getName(): ?string` | `string \| null` | public     | Возвращает локаль. При первом обращении вызывает `identify()` и кэширует результат. |                                                                                                                        |
| `identify(): bool \| string` *(абстрактный)* | `bool \| string`   | protected | Реализуется в наследниках; должна вернуть строку локали или `false`, если определить не удалось.|

## Пример наследования

```php
<?php

declare(strict_types=1);

namespace App\I18n;

use Scaleum\i18n\LocaleIdentityAbstract;

class HeaderLocaleIdentity extends LocaleIdentityAbstract
{
    /**
     * Пытаемся определить локаль из заголовка Accept-Language.
     */
    protected function identify(): bool|string
    {
        $accept = $_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? '';
        if ($accept === '') {
            return false; // локаль не определена
        }

        // Берём первую указанную локаль, например "ru-RU,ru;q=0.9,en-US;q=0.8"
        [$raw] = explode(',', $accept);
        return str_replace('-', '_', trim($raw)); // ru_RU
    }
}
```

## Пример использования

```php
<?php

use App\I18n\HeaderLocaleIdentity;

$localeIdentity = new HeaderLocaleIdentity(); // переменная lowerCamelCase

$currentLocale  = $localeIdentity->getName();

echo $currentLocale; // Например: ru_RU
```

## Практические советы

* Метод `identify()` может определять локаль из **заголовков HTTP**, **конфигурационных файлов**, **сессии пользователя** или другого источника.
* Если локаль не удаётся определить, возвращайте `false` — это позволит вызывающему коду обработать ситуацию по‑своему (например, выбрать локаль по умолчанию).
* Храните идентификатор локали в виде `ll_CC` (например, `en_US`) для совместимости с другими компонентами i18n.

[Вернуться к оглавлению](../../index.md)
