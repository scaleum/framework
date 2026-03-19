[Повернутись до змісту](../index.md)

[EN](../../en/classes/init-trait.md) | **UK** | [RU](../../ru/classes/init-trait.md)
# InitTrait

**InitTrait** — трейд для автоматичної ініціалізації властивостей або виклику сеттерів на основі масиву конфігурації.

## Призначення

- Спрощення ініціалізації об'єктів
- Автоматичне прив’язування конфігураційних параметрів до властивостей
- Виклик сеттерів, якщо вони існують

## Основні можливості

| Метод | Призначення |
|:------|:-----------|
| `init(array $config = [], mixed $context = null)` | Ініціалізація об'єкта або контексту даними з масиву |

## Приклади використання

### Ініціалізація властивостей через масив

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

### Ініціалізація через сеттери

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

## Поведінка

- Якщо існує метод виду `set[PropertyName]`, він викликається.
- Якщо методу немає:
  - Якщо існує властивість, вона встановлюється напряму.
  - Якщо клас — нащадок `\stdClass`, властивість створюється динамічно.
  - В іншому випадку викидається виключення `EPropertyError`.

## Виключення

- `EPropertyError` — якщо властивість не знайдена і об'єкт не є нащадком `\stdClass`.

[Повернутись до змісту](../index.md)