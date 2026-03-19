[Назад](./application.md) | [Повернутися до змісту](../../index.md)

[EN](../../../en/components/console/request.md) | **UK** | [RU](../../../ru/components/console/request.md)
# Request

`Request` — клас для представлення CLI-запиту у фреймворку Scaleum, що реалізує `ConsoleRequestInterface`. Відповідає за збір та надання необроблених аргументів командного рядка.

## Призначення

- Витягувати всі аргументи, передані у скрипт через `$argv`, за винятком імені файлу.
- Надавати доступ до необроблених аргументів через єдиний метод.

## Властивості

| Властивість    | Тип         | Опис                                   |
|:------------|:------------|:-------------------------------------------|
| `private array $args` | `string[]` | Масив аргументів командного рядка (без імені скрипта). |

## Конструктор

```php
public function __construct()
```
- Читає `$_SERVER['argv']`.
- Видаляє перший елемент (ім'я скрипта) за допомогою `array_slice`.
- Зберігає результат у `$this->args`.

## Методи

### getRawArguments()
```php
public function getRawArguments(): array
```
- Повертає масив необроблених аргументів (`$this->args`).

## Приклад використання

```php
use Scaleum\Console\Request;

$request = new Request();
$args    = $request->getRawArguments();

// При виклику: php script.php foo bar
// $args = ['foo', 'bar'];
```

[Назад](./application.md) | [Повернутися до змісту](../../index.md)