[Back to Contents](../index.md)

**EN** | [UK](../../uk/classes/benchmark.md) | [RU](../../ru/classes/benchmark.md)
#  Benchmark

**Benchmark** — a class for measuring time intervals with microsecond precision.

##  Purpose

- Measuring operation execution time
- Precise marking of start and end points
- Estimating time costs

##  Main features

| Method | Purpose |
|:------|:--------|
| `start(string\|array $point, $timer = null)` | Marking the start point |
| `stop(string\|array $point, ?float $timer = null)` | Marking the end point |
| `elapsed(string $name, int $decimals = 4)` | Calculating time between start and stop |
| `getMarkers()` | Retrieving the list of points |

##  Usage examples

###  Starting and stopping points

```php
$benchmark = new Benchmark();
$benchmark->start('example');

// Code execution
sleep(1);

$benchmark->stop('example');

echo $benchmark->elapsed('example'); // 1.0000
```

###  Starting multiple points

```php
$benchmark->start(['first', 'second']);
// ...
$benchmark->stop(['first', 'second']);
```

###  List of recorded points

```php
$markers = $benchmark->getMarkers();
print_r($markers);
```
[Back to Contents](../index.md)
