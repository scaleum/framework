#  Gettext

**EN** | [UK](../../../../uk/components/i18n/loaders/Gettext.md) | [RU](../../../../ru/components/i18n/loaders/Gettext.md)
Translation loader class for GNU Gettext (`.po`) format. Inherits from `TranslationLoaderAbstract` and implements file parsing using regular expressions, forming a collection of translations as an `ArrayObject`.

##  Methods

| Signature                             | Return Type     | Access    | Purpose                                                                                                                                              |
| ------------------------------------- | --------------- | --------- | ---------------------------------------------------------------------------------------------------------------------------------------------------- |
| `load(string $filename): ArrayObject` | `ArrayObject`   | public    | Loads a `.po` file, performs preliminary validation via `validateFile`, parses `msgid`/`msgstr` pairs, and returns them as an `ArrayObject`.          |
| `decode(string $str): string`         | `string`        | protected | Converts a string from Gettext format: removes quotes, escaped characters (`\n`, `\t`, etc.). Used internally by `load`.                             |

##  Usage Example

```php
<?php

use Scaleum\i18n\Loaders\Gettext;

$loader = new Gettext(); // variable in lowerCamelCase

$messages = $loader->load(__DIR__ . '/messages/ru.po');

// Output the translation of the string "greeting"
echo $messages['greeting']; // Привет
```

##  Example `.po` File

```po
msgid "greeting"
msgstr "Привет"

msgid "farewell"
msgstr "До свидания"
```

##  Usage Tips

* **Performance**: if the volume of translations is large, wrap the loader with a cache (e.g., `PSR-16 Cache`) to avoid repeated disk reads.
* **Validity**: the `validateFile` method of the base class checks for file existence and accessibility but not its correctness. Extend validation if necessary by analyzing `.po` headers.
* **Local Environment**: ensure `.po` files are saved in UTF-8 without BOM. This will prevent encoding issues during parsing.
