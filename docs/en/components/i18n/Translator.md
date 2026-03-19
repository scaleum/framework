[Back to Contents](../../index.md)

**EN** | [UK](../../../uk/components/i18n/Translator.md) | [RU](../../../ru/components/i18n/Translator.md)
#  Translator

`Translator` is the central translation extraction service in **Scaleum** projects. It stores a list of translation files, a message cache, and determines the locale through the `LocaleIdentity` strategy. Resource loaders are provided via `LoaderDispatcher`.

##  Properties

| Property             | Type                                               | Access    | Purpose                                                               |
| -------------------- | ------------------------------------------------- | --------- | --------------------------------------------------------------------- |
| `$files`             | `array<string,array{type:string,filename:string}>` | protected | Registered translation files grouped by *text-domain*.                |
| `$loaderDispatcher`  | `LoaderDispatcher\|null`                          | protected | Lazy factory-locator of loaders (`Gettext`, `Ini`, `PhpArray`, etc.). |
| `$locale`            | `string\|null`                                    | protected | Current locale (determined on first access).                          |
| `$localeBase`        | `string`                                          | protected | Folder where locale directories reside (default `messages`).          |
| `$localeIdentity`    | `LocaleIdentityInterface\|null`                   | protected | Locale determination strategy (default `LocaleDetector`).             |
| `$messages`          | `array<string,array<string,ArrayObject>>`         | protected | Cache of strings `[textDomain][locale] → ArrayObject`.                |
| `$fileResolver`      | `FileResolver\|null`                              | protected | File lookup inside the locale directory.                              |

##  Key Methods (public)

| Signature                                                                                   | Return Type        | Purpose                                                                |
| ------------------------------------------------------------------------------------------- | ------------------ | --------------------------------------------------------------------- |
| `addTranslationFile(string $type, string $filename, string $textDomain = 'default'): self`  | `self`             | Registers a translation file of the specified *loader* type for a text domain. |
| `translate(string $message, string $textDomain = 'default', ?string $locale = null)`        | `string`           | Returns the translation of the string or the original text if not found. |
| `getLocale(): string`                                                                       | `string`           | Current locale; computed via `LocaleIdentity` on first access.        |
| `setLocale(string $locale): void`                                                           | `void`             | Forces the locale to be set.                                           |
| `getLocaleDir(?string $locale = null): string`                                              | `string`           | Returns the path to the resource directory of the specified locale.   |
| `getLoaderDispatcher(): LoaderDispatcher`                                                   | `LoaderDispatcher` | Returns (or lazily creates) the loader dispatcher.                     |
| `setLoaderDispatcher(LoaderDispatcher $instance): self`                                     | `self`             | Allows injecting a custom dispatcher (DI/tests).                      |
| `getMessages(): array`                                                                      | `array`            | Returns the cache of loaded messages.                                 |

###  Internal (protected)

| Signature                                                          | Purpose                                                                 |
| ------------------------------------------------------------------ | ----------------------------------------------------------------------- |
| `getTranslation(string $msg, string $domain, string $loc): mixed`  | Retrieves the translation from cache, triggers `loadTranslation()` if needed. |
| `loadTranslation(string $domain, string $loc): bool`               | Loads resources from files using `LoaderDispatcher` and `FileResolver`. |
| `setFiles(array $files): self`                                     | Bulk registration of translation files.                                |

##  Usage Example

```php
<?php

declare(strict_types=1);

use Scaleum\i18n\Translator;
use Scaleum\i18n\LoaderDispatcher;

$loaderDispatcher = new LoaderDispatcher(); // переменная lowerCamelCase
$translator       = new Translator($loaderDispatcher); // переменная lowerCamelCase

// Register translation resources
$translator->addTranslationFile('ini', __DIR__ . '/messages/ru.ini');
$translator->addTranslationFile('ini', __DIR__ . '/messages/en.ini');

// Set locale and get translation
$translator->setLocale('ru_RU');

echo $translator->translate('greeting'); // Привет
```

##  Integration Tips

* **Text‑domain**: group messages by modules or packages by passing your own `$textDomain` to `addTranslationFile()`.
* **Cache**: `Translator` stores translations in process memory; for production, you can extend the class and serialize `$messages` into APCu/files.
* **DI**: register a single instance of `Translator` in the контейнер to avoid duplicate caches.

[Back to Contents](../../index.md)
