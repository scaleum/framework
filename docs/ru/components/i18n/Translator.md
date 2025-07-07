[Вернуться к оглавлению](../../index.md)

# Translator

`Translator` — центральный сервис извлечения переводов в проектах **Scaleum**. Он хранит список файлов перевода, кэш сообщений и определяет локаль через стратегию `LocaleIdentity`. Загрузчики ресурсов предоставляются через `LoaderDispatcher`.

## Свойства

| Свойство            | Тип                                                | Доступ    | Назначение                                                            |
| ------------------- | -------------------------------------------------- | --------- | --------------------------------------------------------------------- |
| `$files`            | `array<string,array{type:string,filename:string}>` | protected | Зарегистрированные файлы перевода, сгруппированные по *text‑domain*.  |
| `$loaderDispatcher` | `LoaderDispatcher\|null`                           | protected | Ленивая фабрика‑локатор загрузчиков (`Gettext`, `Ini`, `PhpArray` …). |
| `$locale`           | `string\|null`                                     | protected | Текущая локаль (определяется при первом обращении).                   |
| `$localeBase`       | `string`                                           | protected | Папка, где лежат каталоги локалей (`messages` по умолчанию).          |
| `$localeIdentity`   | `LocaleIdentityInterface\|null`                    | protected | Стратегия определения локали (по умолчанию `LocaleDetector`).         |
| `$messages`         | `array<string,array<string,ArrayObject>>`          | protected | Кэш строк `[textDomain][locale] → ArrayObject`.                       |
| `$fileResolver`     | `FileResolver\|null`                               | protected | Поиск файлов внутри каталога локали.                                  |

## Ключевые методы (public)

| Подпись                                                                                    | Возвращаемый тип   | Назначение                                                               |
| ------------------------------------------------------------------------------------------ | ------------------ | ------------------------------------------------------------------------ |
| `addTranslationFile(string $type, string $filename, string $textDomain = 'default'): self` | `self`             | Регистрирует файл перевода указанного *loader*-типа для текст‑домена.    |
| `translate(string $message, string $textDomain = 'default', ?string $locale = null)`       | `string`           | Возвращает перевод строки или исходный текст, если перевод не найден.    |
| `getLocale(): string`                                                                      | `string`           | Текущая локаль; вычисляется через `LocaleIdentity` при первом обращении. |
| `setLocale(string $locale): void`                                                          | `void`             | Принудительно задаёт локаль.                                             |
| `getLocaleDir(?string $locale = null): string`                                             | `string`           | Возвращает путь к каталогу ресурсов указанной локали.                    |
| `getLoaderDispatcher(): LoaderDispatcher`                                                  | `LoaderDispatcher` | Возвращает (или лениво создаёт) диспетчер загрузчиков.                   |
| `setLoaderDispatcher(LoaderDispatcher $instance): self`                                    | `self`             | Позволяет внедрить собственный диспетчер (DI/тесты).                     |
| `getMessages(): array`                                                                     | `array`            | Возвращает кэш загруженных сообщений.                                    |

### Внутренние (protected)

| Подпись                                                           | Назначение                                                                |
| ----------------------------------------------------------------- | ------------------------------------------------------------------------- |
| `getTranslation(string $msg, string $domain, string $loc): mixed` | Берёт перевод из кэша, при необходимости триггерит `loadTranslation()`.   |
| `loadTranslation(string $domain, string $loc): bool`              | Загружает ресурсы файлами, используя `LoaderDispatcher` и `FileResolver`. |
| `setFiles(array $files): self`                                    | Массовая регистрация translation‑файлов.                                  |

## Пример использования

```php
<?php

declare(strict_types=1);

use Scaleum\i18n\Translator;
use Scaleum\i18n\LoaderDispatcher;

$loaderDispatcher = new LoaderDispatcher(); // переменная lowerCamelCase
$translator       = new Translator($loaderDispatcher); // переменная lowerCamelCase

// Регистрируем переводческие ресурсы
$translator->addTranslationFile('ini', __DIR__ . '/messages/ru.ini');
$translator->addTranslationFile('ini', __DIR__ . '/messages/en.ini');

// Устанавливаем локаль и получаем перевод
$translator->setLocale('ru_RU');

echo $translator->translate('greeting'); // Привет
```

## Советы по интеграции

* **Text‑domain**: группируйте сообщения по модулям или пакетам, передавая собственный `$textDomain` в `addTranslationFile()`.
* **Кэш**: `Translator` хранит переводы в памяти процесса; для production можно расширить класс и сериализовать `$messages` в APCu/файлы.
* **DI**: регистрируйте единственный экземпляр `Translator` в контейнере, чтобы избежать дублирующих кэшей.

[Вернуться к оглавлению](../../index.md)
