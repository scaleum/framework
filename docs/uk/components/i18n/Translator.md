[Повернутись до змісту](../../index.md)

[EN](../../../en/components/i18n/Translator.md) | **UK** | [RU](../../../ru/components/i18n/Translator.md)
#  Translator

`Translator` — центральний сервіс вилучення перекладів у проєктах **Scaleum**. Він зберігає список файлів перекладу, кеш повідомлень і визначає локаль через стратегію `LocaleIdentity`. Завантажувачі ресурсів надаються через `LoaderDispatcher`.

##  Властивості

| Властивість         | Тип                                                | Доступ    | Призначення                                                           |
| ------------------- | -------------------------------------------------- | --------- | -------------------------------------------------------------------- |
| `$files`            | `array<string,array{type:string,filename:string}>` | protected | Зареєстровані файли перекладу, згруповані за *text‑domain*.          |
| `$loaderDispatcher` | `LoaderDispatcher\|null`                           | protected | Лінива фабрика-локатор завантажувачів (`Gettext`, `Ini`, `PhpArray` …). |
| `$locale`           | `string\|null`                                     | protected | Поточна локаль (визначається при першому зверненні).                  |
| `$localeBase`       | `string`                                           | protected | Папка, де лежать каталоги локалей (`messages` за замовчуванням).      |
| `$localeIdentity`   | `LocaleIdentityInterface\|null`                    | protected | Стратегія визначення локалі (за замовчуванням `LocaleDetector`).      |
| `$messages`         | `array<string,array<string,ArrayObject>>`          | protected | Кеш рядків `[textDomain][locale] → ArrayObject`.                      |
| `$fileResolver`     | `FileResolver\|null`                               | protected | Пошук файлів всередині каталогу локалі.                               |

##  Ключові методи (public)

| Підпис                                                                                   | Повертаємий тип   | Призначення                                                             |
| ---------------------------------------------------------------------------------------- | ----------------- | ---------------------------------------------------------------------- |
| `addTranslationFile(string $type, string $filename, string $textDomain = 'default'): self` | `self`            | Реєструє файл перекладу вказаного *loader*-типу для текст-домену.      |
| `translate(string $message, string $textDomain = 'default', ?string $locale = null)`      | `string`          | Повертає переклад рядка або вихідний текст, якщо переклад не знайдено.  |
| `getLocale(): string`                                                                     | `string`          | Поточна локаль; обчислюється через `LocaleIdentity` при першому зверненні. |
| `setLocale(string $locale): void`                                                         | `void`            | Примусово задає локаль.                                                 |
| `getLocaleDir(?string $locale = null): string`                                            | `string`          | Повертає шлях до каталогу ресурсів вказаної локалі.                     |
| `getLoaderDispatcher(): LoaderDispatcher`                                                 | `LoaderDispatcher`| Повертає (або ліниво створює) диспетчер завантажувачів.               |
| `setLoaderDispatcher(LoaderDispatcher $instance): self`                                   | `self`            | Дозволяє впровадити власний диспетчер (DI/тести).                      |
| `getMessages(): array`                                                                    | `array`           | Повертає кеш завантажених повідомлень.                                 |

###  Внутрішні (protected)

| Підпис                                                          | Призначення                                                               |
| ----------------------------------------------------------------| ------------------------------------------------------------------------- |
| `getTranslation(string $msg, string $domain, string $loc): mixed`| Бере переклад з кешу, за потреби тригерить `loadTranslation()`.           |
| `loadTranslation(string $domain, string $loc): bool`             | Завантажує ресурси файлами, використовуючи `LoaderDispatcher` і `FileResolver`. |
| `setFiles(array $files): self`                                   | Масова реєстрація translation-файлів.                                    |

##  Приклад використання

```php
<?php

declare(strict_types=1);

use Scaleum\i18n\Translator;
use Scaleum\i18n\LoaderDispatcher;

$loaderDispatcher = new LoaderDispatcher(); // змінна lowerCamelCase
$translator       = new Translator($loaderDispatcher); // змінна lowerCamelCase

// Реєструємо перекладацькі ресурси
$translator->addTranslationFile('ini', __DIR__ . '/messages/ru.ini');
$translator->addTranslationFile('ini', __DIR__ . '/messages/en.ini');

// Встановлюємо локаль і отримуємо переклад
$translator->setLocale('ru_RU');

echo $translator->translate('greeting'); // Привіт
```

##  Поради щодо інтеграції

* **Text‑domain**: групуйте повідомлення за модулями або пакетами, передаючи власний `$textDomain` у `addTranslationFile()`.
* **Кеш**: `Translator` зберігає переклади в пам’яті процесу; для production можна розширити клас і серіалізувати `$messages` в APCu/файли.
* **DI**: реєструйте єдиний екземпляр `Translator` у контейнері, щоб уникнути дублюючих кешів.

[Повернутися до змісту](../../index.md)
