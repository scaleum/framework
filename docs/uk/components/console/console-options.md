[Назад](./application.md) | [Повернутися до змісту](../../index.md)

[EN](../../../en/components/console/console-options.md) | **UK** | [RU](../../../ru/components/console/console-options.md)
# ConsoleOptions

`ConsoleOptions` — клас для парсингу та керування опціями й аргументами командного рядка в CLI-модулі Scaleum. Розширює `Hydrator` для гнучкої конфігурації через масив і автоматично обробляє передані прапорці.

## Призначення

- Розбирати необроблений масив аргументів (`$argv`) для отримання значень коротких (`-o`, `/o`) та довгих (`--option`) опцій.
- Підтримувати обов’язкові та необов’язкові опції, а також прапорці без значень.
- Забезпечувати зручний доступ до розібраних опцій через методи `get()` і `getAll()`.

## Зв’язок з CommandAbstract

Команди наслідують `CommandAbstract`, де метод `getOptions()` повертає екземпляр `ConsoleOptions`. Команда може налаштувати допустимі опції через методи `setOpts()` і `setOptsLong()` до виклику `execute()`.

## Властивості

| Властивість          | Тип        | Опис                                                      |
|:---------------------|:-----------|:----------------------------------------------------------|
| `private array $args`         | `string[]` | Вхідний масив аргументів (`$argv`).                       |
| `private int $args_count`     | `int`      | Кількість елементів у масиві `$args`.                     |
| `private array $opts`         | `string[]` | Список допустимих коротких опцій (`-o`).                  |
| `private array $opts_long`    | `string[]` | Список допустимих довгих опцій (`--option`).               |
| `private array $opts_parsed`  | `mixed[]`  | Результат парсингу: ключі — імена опцій, значення — їхні значення або прапорці. |

### Константи

| Константа               | Значення | Опис                                               |
|:------------------------|:---------|:---------------------------------------------------|
| `OPT_REQUIRED`          | `1 << 1` | Опція обов’язкова і очікує значення (`:`).          |
| `OPT_NOT_REQUIRED`      | `1 << 2` | Опція необов’язкова, значення необов’язкове (`::`).  |
| `OPT_EMPTY`             | `1 << 3` | Прапорець без значення.                             |

## Методи

### __construct
```php
public function __construct(array $config = [])
```
- Приймає конфігурацію через `Hydrator`: можна задати `args`, `opts`, `opts_long`.
- Викликає `parse()` для автоматичної обробки.

### parse
```php
public function parse(): static
```
- Формує внутрішній масив `$opts_parsed` на основі `opts` і `opts_long`.
- Визначає зсув початку аргументів, пропускаючи ім’я скрипта.
- Перебирає всі аргументи, аналізує формат (`--long=value`, `-s value`), перевіряє допустимість і присвоює значення або прапорець.

### get
```php
public function get(string $option, mixed $default = null): mixed
```
- Повертає значення опції `$option` з `$opts_parsed`, або `$default`, якщо не встановлена.

### getAll
```php
public function getAll(): array
```
- Повертає весь масив розібраних опцій.

### setArgs
```php
public function setArgs(array $args): static
```
- Встановлює користувацький масив `$args` замість глобального `$argv`.
- Оновлює `$args_count`.

### setOpts / setOptsLong
```php
public function setOpts(array $opts): static
public function setOptsLong(array $optsLong): static
```
- Задає список коротких або довгих опцій.
- Застосовує `sanitizeOptionValue` до кожного елемента.

### sanitizeOptionValue
```php
private function sanitizeOptionValue(array|string $val)
```
- Видаляє провідні символи `-`, `--`, `=`, пробіли зі рядка.

## Приклад використання

```php
// У команді:
class SampleCommand extends CommandAbstract {
    public function execute(ConsoleRequestInterface $request): ConsoleResponseInterface {
        // Допустимі опції: -v (прапорець), -o: (обов’язкова), --filter:: (необов’язкова)
        $this->getOptions()
             ->setOpts(['v', 'o:'])
             ->setOptsLong(['filter::']);

        // Парсинг $argv
        $opts = $this->getOptions()->parse();

        if ($opts->get('v') ?? false) {
            $this->printLine("Verbose mode enabled");
        }

        $outputFile = $opts->get('o') ?? 'default.txt';
        $filters    = $opts->get('filter') ?? [];

        // ... логіка команди ...

        $response = new Response();
        $response->setStatusCode(Response::STATUS_SUCCESS);
        return $response;
    }
}
```

[Назад](./application.md) | [Повернутися до змісту](../../index.md)