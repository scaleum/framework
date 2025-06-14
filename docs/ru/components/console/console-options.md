[Назад](./application.md) | [Вернуться к оглавлению](../../index.md)
# ConsoleOptions

`ConsoleOptions` — класс для парсинга и управления опциями и аргументами командной строки в CLI-модуле Scaleum. Расширяет `Hydrator` для гибкой конфигурации через массив и автоматически обрабатывает переданные флаги.

## Назначение

- Разбирать необработанный массив аргументов (`$argv`) для получения значений коротких (`-o`, `/o`) и длинных (`--option`) опций.
- Поддерживать обязательные и необязательные опции, а также флаги без значений.
- Предоставлять удобный доступ к разобранным опциям через методы `get()` и `getAll()`.

## Связь с CommandAbstract

Команды наследуют `CommandAbstract`, где метод `getOptions()` возвращает экземпляр `ConsoleOptions`. Команда может настроить допустимые опции через методы `setOpts()` и `setOptsLong()` до вызова `execute()`.

## Свойства

| Свойство            | Тип       | Описание                                                    |
|:--------------------|:----------|:------------------------------------------------------------|
| `private array $args`         | `string[]` | Входной массив аргументов (`$argv`).                         |
| `private int $args_count`     | `int`      | Число элементов в массиве `$args`.                           |
| `private array $opts`         | `string[]` | Список допустимых коротких опций (`-o`). |
| `private array $opts_long`    | `string[]` | Список допустимых длинных опций (`--option`).    |
| `private array $opts_parsed`  | `mixed[]`  | Результат парсинга: ключи — имена опций, значения — их значения или флаги. |

### Константы

| Константа               | Значение | Описание                                           |
|:------------------------|:---------|:---------------------------------------------------|
| `OPT_REQUIRED`          | `1 << 1` | Опция обязательна и ожидает значение (`:`).        |
| `OPT_NOT_REQUIRED`      | `1 << 2` | Опция не обязательна, значение необязательно (`::`).|
| `OPT_EMPTY`             | `1 << 3` | Флаг без значения.                                  |

## Методы

### __construct
```php
public function __construct(array $config = [])
```
- Принимает конфигурацию через `Hydrator`: можно задать `args`, `opts`, `opts_long`.
- Вызывает `parse()` для автоматической обработки.

### parse
```php
public function parse(): static
```
- Формирует внутренний массив `$opts_parsed` на основе `opts` и `opts_long`.
- Определяет смещение начала аргументов, пропуская имя скрипта.
- Перебирает все аргументы, анализирует формат (`--long=value`, `-s value`), проверяет допустимость и присваивает значение или флаг.

### get
```php
public function get(string $option, mixed $default = null): mixed
```
- Возвращает значение опции `$option` из `$opts_parsed`, либо `$default`, если не установлена.

### getAll
```php
public function getAll(): array
```
- Возвращает весь массив разобранных опций.

### setArgs
```php
public function setArgs(array $args): static
```
- Устанавливает пользовательский массив `$args` вместо глобального `$argv`.
- Обновляет `$args_count`.

### setOpts / setOptsLong
```php
public function setOpts(array $opts): static
public function setOptsLong(array $optsLong): static
```
- Задаёт список коротких или длинных опций.
- Применяет `sanitizeOptionValue` к каждому элементу.

### sanitizeOptionValue
```php
private function sanitizeOptionValue(array|string $val)
```
- Убирает ведущие символы `-`, `--`, `=`, пробелы из строки.

## Пример использования

```php
// В команде:
class SampleCommand extends CommandAbstract {
    public function execute(ConsoleRequestInterface $request): ConsoleResponseInterface {
        // Допустимые опции: -v (флаг), -o: (обязательное), --filter:: (необязательно)
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

        // ... логика команды ...

        $response = new Response();
        $response->setStatusCode(Response::STATUS_SUCCESS);
        return $response;
    }
}
```

[Назад](./application.md) | [Вернуться к оглавлению](../../index.md)

