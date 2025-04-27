[Вернуться к оглавлению](../index.md)
# InitTrait

**InitTrait** — трейд для автоматической инициализации свойств или вызова сеттеров на основе массива конфигурации.

## Назначение

- Упрощение инициализации объектов
- Автоматическая привязка конфигурационных параметров к свойствам
- Вызов сеттеров, если они существуют

## Основные возможности

| Метод | Назначение |
|:------|:-----------|
| `init(array $config = [], mixed $context = null)` | Инициализация объекта или контекста данными из массива |

## Примеры использования

### Инициализация свойств через массив

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

### Инициализация через сеттеры

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

## Поведение

- Если существует метод вида `set[PropertyName]`, он вызывается.
- Если метода нет:
  - Если существует свойство, оно устанавливается напрямую.
  - Если класс — потомок `\stdClass`, свойство создается динамически.
  - В противном случае выбрасывается исключение `EPropertyError`.

## Исключения

- `EPropertyError` — если свойство не найдено и объект не является потомком `\stdClass`.

[Вернуться к оглавлению](../index.md)

