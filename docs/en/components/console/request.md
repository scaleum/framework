[Back](./application.md) | [Back to Contents](../../index.md)

**EN** | [UK](../../../uk/components/console/request.md) | [RU](../../../ru/components/console/request.md)
#  Request

`Request` is a class for representing a CLI request in the Scaleum framework, implementing `ConsoleRequestInterface`. It is responsible for collecting and providing raw command line arguments.

##  Purpose

- Extract all arguments passed to the script via `$argv`, except for the filename.
- Provide access to raw arguments through a single method.

##  Properties

| Property    | Type         | Description                                   |
|:------------|:------------|:-------------------------------------------|
| `private array $args` | `string[]` | Array of command line arguments (excluding the script name). |

##  Constructor

```php
public function __construct()
```
- Reads `$_SERVER['argv']`.
- Removes the first element (script name) using `array_slice`.
- Stores the result in `$this->args`.

##  Methods

###  getRawArguments()
```php
public function getRawArguments(): array
```
- Returns the array of raw arguments (`$this->args`).

##  Usage Example

```php
use Scaleum\Console\Request;

$request = new Request();
$args    = $request->getRawArguments();

// When called: php script.php foo bar
// $args = ['foo', 'bar'];
```

[Back](./application.md) | [Back to Contents](../../index.md)