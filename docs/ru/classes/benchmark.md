[Вернуться к оглавлению](../index.md)
# Benchmark

**Benchmark** — класс для измерения временных интервалов с точностью до микросекунд.

## Назначение

- Измерение времени выполнения операций
- Точечная фиксация начала и завершения шагов
- Оценка временных затрат

## Основные возможности

| Метод | Назначение |
|:------|:-----------|
| `start(string\|array $point, $timer = null)` | Фиксация точки начала |
| `stop(string\|array $point, ?float $timer = null)` | Фиксация точки завершения |
| `elapsed(string $name, int $decimals = 4)` | Вычисление времени между start и stop |
| `getMarkers()` | Получение списка точек |

## Примеры использования

### Старт и стоп точек

```php
$benchmark = new Benchmark();
$benchmark->start('example');

// Выполнение кода
sleep(1);

$benchmark->stop('example');

echo $benchmark->elapsed('example'); // 1.0000
```

### Старт нескольких точек

```php
$benchmark->start(['first', 'second']);
// ...
$benchmark->stop(['first', 'second']);
```

### Список зафиксированных точек

```php
$markers = $benchmark->getMarkers();
print_r($markers);
```
[Вернуться к оглавлению](../index.md)
