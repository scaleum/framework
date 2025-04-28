[Вернуться к оглавлению](../../../index.md)
# Template

`Template` — класс-обёртка для работы с файловыми шаблонами: хранит путь к файлу, данные для рендеринга, результат сформированного контента и режим частичного включения.

## Назначение

- Загрузка и хранение имени файла шаблона.
- Передача ассоциативного массива данных для подстановки в шаблон.
- Отметка шаблона как частичного (`partial`), без обёртки макетом.
- Хранение результата рендеринга в свойстве `content`.
- Приведение объекта к строке (`__toString`) возвращает содержимое.

## Конструктор

```php
public function __construct(
    string $filename,
    array  $data    = [],
    bool   $partial = false
)
```
- `$filename` — путь к файлу шаблона (например, `views/user/profile.tmpl`).
- `$data` — ассоциативный массив переменных, доступных внутри шаблона.
- `$partial` — если `true`, шаблон рендерится без обёрток (layout).

## Свойства и методы

| Метод                                    | Описание                                                             |
|:-----------------------------------------|:---------------------------------------------------------------------|
| `getFilename(): string`                  | Возвращает путь к файлу шаблона.                                     |
| `setFilename(string $filename): self`    | Устанавливает путь к файлу шаблона.                                  |
| `getData(): array`                       | Возвращает массив данных для шаблона.                                |
| `setData(array $data): self`             | Устанавливает данные для подстановки.                                |
| `getPartial(): bool`                     | Возвращает флаг частичного рендеринга.                                |
| `setPartial(bool $partial): self`        | Устанавливает режим частичного рендеринга.                           |
| `getContent(): string`                   | Возвращает результат рендеринга (сформированный HTML).               |
| `setContent(string $content): self`      | Устанавливает результат рендеринга.                                  |
| `__toString(): string`                   | Позволяет выводить объект как строку HTML: `(string)$template` = content. |

## Пример использования

### 1. Ручная загрузка и рендеринг шаблона
```php
$template = new Template(
    __DIR__ . '/views/user/profile.tmpl',
    ['user' => $userEntity],
    false
);

// Внутри рендерера:
ob_start();
extract($template->getData(), EXTR_SKIP);
include $template->getFilename();
$html = ob_get_clean();

$template->setContent($html);
echo $template; // выводит готовый HTML
```

### 2. Частичный шаблон без layout
```php
$partial = new Template(
    __DIR__ . '/views/partials/navbar.tmpl',
    ['activePage' => 'home'],
    true
);

ob_start();
extract($partial->getData());
include $partial->getFilename();
$partial->setContent(ob_get_clean());

echo $partial; // выводит только HTML навигации
```

### 3. Использование в Middleware или Renderer-классе
```php
class ViewRenderer {
    public function render(Template $tpl): string {
        if (! file_exists($tpl->getFilename())) {
            throw new RuntimeException('Template not found: ' . $tpl->getFilename());
        }

        ob_start();
        extract($tpl->getData(), EXTR_SKIP);
        include $tpl->getFilename();
        $tpl->setContent(ob_get_clean());

        return (string)$tpl;
    }
}

$renderer = new ViewRenderer();
echo $renderer->render(
    new Template('views/home.php', ['title'=>'Welcome'])
);
```

[Вернуться к оглавлению](../../../index.md)