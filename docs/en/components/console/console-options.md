[Back](./application.md) | [Return to Contents](../../index.md)

**EN** | [UK](../../../uk/components/console/console-options.md) | [RU](../../../ru/components/console/console-options.md)
#  ConsoleOptions

`ConsoleOptions` is a class for parsing and managing command-line options and arguments in the Scaleum CLI module. It extends `Hydrator` for flexible configuration via an array and automatically processes passed flags.

##  Purpose

- Parse the raw arguments array (`$argv`) to extract values of short (`-o`, `/o`) and long (`--option`) options.
- Support required and optional options, as well as flags without values.
- Provide convenient access to parsed options via `get()` and `getAll()` methods.

##  Relation to CommandAbstract

Commands inherit from `CommandAbstract`, where the `getOptions()` method returns an instance of `ConsoleOptions`. The command can configure allowed options via `setOpts()` and `setOptsLong()` methods before calling `execute()`.

##  Properties

| Property             | Type       | Description                                               |
|:---------------------|:-----------|:----------------------------------------------------------|
| `private array $args`         | `string[]` | Input arguments array (`$argv`).                           |
| `private int $args_count`     | `int`      | Number of elements in the `$args` array.                   |
| `private array $opts`         | `string[]` | List of allowed short options (`-o`).                      |
| `private array $opts_long`    | `string[]` | List of allowed long options (`--option`).                 |
| `private array $opts_parsed`  | `mixed[]`  | Parsing result: keys are option names, values are their values or flags. |

###  Constants

| Constant               | Value    | Description                                           |
|:-----------------------|:---------|:------------------------------------------------------|
| `OPT_REQUIRED`          | `1 << 1` | Option is required and expects a value (`:`).         |
| `OPT_NOT_REQUIRED`      | `1 << 2` | Option is optional, value is optional (`::`).          |
| `OPT_EMPTY`             | `1 << 3` | Flag without a value.                                  |

##  Methods

###  __construct
```php
public function __construct(array $config = [])
```
- Accepts configuration via `Hydrator`: can set `args`, `opts`, `opts_long`.
- Calls `parse()` for automatic processing.

###  parse
```php
public function parse(): static
```
- Builds the internal `$opts_parsed` array based on `opts` and `opts_long`.
- Determines the offset for arguments start, skipping the script name.
- Iterates over all arguments, analyzes format (`--long=value`, `-s value`), checks validity, and assigns value or flag.

###  get
```php
public function get(string $option, mixed $default = null): mixed
```
- Returns the value of the `$option` from `$opts_parsed`, or `$default` if not set.

###  getAll
```php
public function getAll(): array
```
- Returns the entire array of parsed options.

###  setArgs
```php
public function setArgs(array $args): static
```
- Sets a custom `$args` array instead of the global `$argv`.
- Updates `$args_count`.

###  setOpts / setOptsLong
```php
public function setOpts(array $opts): static
public function setOptsLong(array $optsLong): static
```
- Sets the list of short or long options.
- Applies `sanitizeOptionValue` to each element.

###  sanitizeOptionValue
```php
private function sanitizeOptionValue(array|string $val)
```
- Removes leading characters `-`, `--`, `=`, and spaces from the string.

##  Usage Example

```php
// In a command:
class SampleCommand extends CommandAbstract {
    public function execute(ConsoleRequestInterface $request): ConsoleResponseInterface {
        // Allowed options: -v (flag), -o: (required), --filter:: (optional)
        $this->getOptions()
             ->setOpts(['v', 'o:'])
             ->setOptsLong(['filter::']);

        // Parsing $argv
        $opts = $this->getOptions()->parse();

        if ($opts->get('v') ?? false) {
            $this->printLine("Verbose mode enabled");
        }

        $outputFile = $opts->get('o') ?? 'default.txt';
        $filters    = $opts->get('filter') ?? [];

        // ... command logic ...

        $response = new Response();
        $response->setStatusCode(Response::STATUS_SUCCESS);
        return $response;
    }
}
```

[Back](./application.md) | [Return to Contents](../../index.md)

