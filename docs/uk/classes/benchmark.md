[Вернутися до змісту](../index.md)

[EN](../../en/classes/benchmark.md) | **UK** | [RU](../../ru/classes/benchmark.md)
# Benchmark

**Benchmark** — клас для вимірювання часових інтервалів з точністю до мікросекунд.

## Призначення

- Вимірювання часу виконання операцій
- Точкова фіксація початку та завершення кроків
- Оцінка часових витрат

## Основні можливості

| Метод | Призначення |
|:------|:------------|
| `start(string\|array $point, $timer = null)` | Фіксація точки початку |
| `stop(string\|array $point, ?float $timer = null)` | Фіксація точки завершення |
| `elapsed(string $name, int $decimals = 4)` | Обчислення часу між start і stop |
| `getMarkers()` | Отримання списку точок |

## Приклади використання

### Старт і стоп точок

```php
$benchmark = new Benchmark();
$benchmark->start('example');

// Виконання коду
sleep(1);

$benchmark->stop('example');

echo $benchmark->elapsed('example'); // 1.0000
```

### Старт кількох точок

```php
$benchmark->start(['first', 'second']);
// ...
$benchmark->stop(['first', 'second']);
```

### Список зафіксованих точок

```php
$markers = $benchmark->getMarkers();
print_r($markers);
```
[Вернутися до змісту](../index.md)