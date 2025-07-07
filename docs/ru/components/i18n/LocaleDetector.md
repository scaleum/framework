[Вернуться к оглавлению](../../index.md)

# LocaleDetector

`LocaleDetector` наследует `LocaleIdentityAbstract` и автоматически определяет текущую локаль приложения. Опирается на переменные окружения (`LANG`, `LC_ALL`, `LC_CTYPE`), заголовок `Accept-Language` (при работе в веб‑среде) и системную locale. При необходимости нормализует строку в формат `ll_CC` и добавляет страну по умолчанию.

## Свойства

| Свойство            | Тип                    | Доступ         | Назначение                          |
| ------------------- | ---------------------- | -------------- | ----------------------------------- |
| `$defaultCountries` | `array<string,string>` | private static | Соответствие ISO‑639‑1 кода языка к ISO‑3166‑1 коду страны по умолчанию (например, `'ru' => 'RU'`). Используется в `normalizeLocale()` для дополнения недостающей части «страна».|

## Методы

| Подпись | Возвращаемый тип | Доступ         | Назначение |
|---|---|---|---|
| `identify(): bool\| string`| `bool\| string` | protected | Определяет локаль в следующем порядке: переменные окружения → заголовок `Accept‑Language` → `setlocale(LC_ALL, "0")`. Возвращает `false`, если локаль не удалось получить.|
| `getName(): ?string` *(унаследован)*          | `string \| null\`         | public | Возвращает кэшированную локаль; при первом вызове обращается к `identify()`.|
| `normalizeLocale(string $locale): string`     | `string`         | private static | Приводит строку к виду `ll_CC`: убирает кодировку (`.UTF‑8` и т.д.), добавляет страну по умолчанию, приводит регистр символов.|
| `getDefaultCountry(string $language): string` | `string`         | private static | Возвращает страну по умолчанию из `$defaultCountries` или `'US'`, если язык неизвестен.|

## Пример использования

```php
<?php

declare(strict_types=1);

use Scaleum\i18n\LocaleDetector;

$localeDetector = new LocaleDetector(); // переменная lowerCamelCase

$currentLocale  = $localeDetector->getName(); // Например: ru_RU

echo $currentLocale;
```
> Изменение статического свойства влияет на все экземпляры `LocaleDetector`, созданные после изменения.

[Вернуться к оглавлению](../../index.md)
