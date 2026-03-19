[Back to Contents](../index.md)

**EN** | [UK](../../uk/classes/collection.md) | [RU](../../ru/classes/collection.md)
#  Collection

**Collection** — a universal class for working with data collections in the style of arrays and objects.

##  Purpose

- Managing data sets (elements)
- Support for interfaces `Iterator`, `ArrayAccess`, `Countable`
- Serialization to string or XML
- Flexible access and navigation methods

##  Main Features

| Method | Purpose |
|:------|:--------|
| `__construct(?array $array = null)` | Initialize the collection with data |
| `__toArray()` | Convert the collection to an array |
| `__toString()` | Serialize the collection to a string |
| `__toXml()` | Convert the collection to XML |
| `set($key, $item)` | Set an element |
| `get($key, $default = null)` | Get an element by key |
| `remove($key)` | Remove an element |
| `clear()` | Clear all elements |
| `count()` | Number of elements |
| `exists($key)` | Check if a key exists |
| `isEmpty()` | Check if the collection is empty |
| `asort()`, `ksort()`, `sort()` | Sort the collection |
| `merge(array $array, bool $overwrite = false)` | Merge the collection with an array |
| `fetch()` | Get the current element and advance the pointer |
| `seek($key)` | Find an element by key |
| `uasort($callback)` | Sort using a user-defined function |

##  Usage Examples

###  Creating a Collection

```php
$collection = new Collection(['one' => 1, 'two' => 2]);
```

###  Adding an Element

```php
$collection->set('three', 3);
```

###  Getting an Element

```php
$value = $collection->get('one'); // 1
```

###  Checking if a Key Exists

```php
if ($collection->exists('two')) {
    echo 'Key two exists';
}
```

###  Converting to an Array

```php
$array = $collection->__toArray();
```

###  Converting to XML

```php
$xml = $collection->__toXml();
echo $xml;
```

###  Clearing the Collection

```php
$collection->clear();
```

###  Iterating Over the Collection

```php
foreach ($collection as $key => $value) {
    echo "$key: $value\n";
}
```

##  Exceptions

- No specific exceptions in the base implementation.

[Back to Contents](../index.md)

