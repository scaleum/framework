[Back to Contents](../index.md)

**EN** | [UK](../../uk/classes/init-trait.md) | [RU](../../ru/classes/init-trait.md)
#  InitTrait

**InitTrait** — trait for automatic initialization of properties or calling setters based on a configuration array.

##  Purpose

- Simplifying object initialization
- Automatic binding of configuration parameters to properties
- Calling setters if they exist

##  Main features

| Method | Purpose |
|:------|:--------|
| `init(array $config = [], mixed $context = null)` | Initialization of an object or context with data from an array |

##  Usage examples

###  Initializing properties via array

```php
class ExampleClass {
    use InitTrait;

    protected string $name;
    protected int $age;

    public function __construct(array $config = []) {
        $this->init($config);
    }
}

$example = new ExampleClass(['name' => 'John', 'age' => 30]);
```

###  Initialization via setters

```php
class ExampleWithSetters {
    use InitTrait;

    protected string $title;

    public function setTitle(string $title): void {
        $this->title = strtoupper($title);
    }
}

$example = new ExampleWithSetters();
$example->init(['title' => 'developer']);
```

##  Behavior

- If a method of the form `set[PropertyName]` exists, it is called.
- If the method does not exist:
  - If the property exists, it is set directly.
  - If the class is a descendant of `\stdClass`, the property is created dynamically.
  - Otherwise, an `EPropertyError` exception is thrown.

##  Exceptions

- `EPropertyError` — if the property is not found and the object is not a descendant of `\stdClass`.

[Back to Contents](../index.md)

