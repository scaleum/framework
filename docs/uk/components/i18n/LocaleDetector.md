[Повернутись до змісту](../../index.md)

[EN](../../../en/components/i18n/LocaleDetector.md) | **UK** | [RU](../../../ru/components/i18n/LocaleDetector.md)
# LocaleDetector

`LocaleDetector` наслідує `LocaleIdentityAbstract` і автоматично визначає поточну локаль застосунку. Спирається на змінні оточення (`LANG`, `LC_ALL`, `LC_CTYPE`), заголовок `Accept-Language` (під час роботи у веб-середовищі) та системну locale. За потреби нормалізує рядок у формат `ll_CC` і додає країну за замовчуванням.

## Властивості

| Властивість         | Тип                    | Доступ         | Призначення                          |
| ------------------- | ---------------------- | -------------- | ----------------------------------- |
| `$defaultCountries` | `array<string,string>` | private static | Відповідність ISO‑639‑1 коду мови до ISO‑3166‑1 коду країни за замовчуванням (наприклад, `'ru' => 'RU'`). Використовується у `normalizeLocale()` для доповнення відсутньої частини «країна».|

## Методи

| Підпис | Тип повернення | Доступ         | Призначення |
|---|---|---|---|
| `identify(): bool\| string`| `bool\| string` | protected | Визначає локаль у такому порядку: змінні оточення → заголовок `Accept-Language` → `setlocale(LC_ALL, "0")`. Повертає `false`, якщо локаль не вдалося отримати.|
| `getName(): ?string` *(успадкований)*          | `string \| null\`         | public | Повертає кешовану локаль; при першому виклику звертається до `identify()`.|
| `normalizeLocale(string $locale): string`     | `string`         | private static | Приводить рядок до вигляду `ll_CC`: видаляє кодування (`.UTF-8` тощо), додає країну за замовчуванням, приводить регістр символів.|
| `getDefaultCountry(string $language): string` | `string`         | private static | Повертає країну за замовчуванням із `$defaultCountries` або `'US'`, якщо мова невідома.|

## Приклад використання

```php
<?php

declare(strict_types=1);

use Scaleum\i18n\LocaleDetector;

$localeDetector = new LocaleDetector(); // змінна lowerCamelCase

$currentLocale  = $localeDetector->getName(); // Наприклад: ru_RU

echo $currentLocale;
```
> Зміна статичної властивості впливає на всі екземпляри `LocaleDetector`, створені після зміни.

[Повернутись до змісту](../../index.md)