[Повернутись до змісту](../../../index.md)

[EN](../../../../en/components/http/renderers/template.md) | **UK** | [RU](../../../../ru/components/http/renderers/template.md)
# Template

`Template` — клас-обгортка для роботи з файловими шаблонами: зберігає шлях до файлу, дані для рендерингу, результат сформованого контенту та режим часткового включення.

## Призначення

- Завантаження та зберігання імені файлу шаблона.
- Передача асоціативного масиву даних для підстановки в шаблон.
- Позначення шаблона як часткового (`partial`), без обгортки макетом.
- Зберігання результату рендерингу у властивості `content`.
- Приведення об’єкта до рядка (`__toString`) повертає вміст.

## Конструктор

```php
public function __construct(
    string $filename,
    array  $data    = [],
    bool   $partial = false
)
```
- `$filename` — шлях до файлу шаблона (наприклад, `views/user/profile.tmpl`).
- `$data` — асоціативний масив змінних, доступних всередині шаблона.
- `$partial` — якщо `true`, шаблон рендериться без обгорток (layout).

## Властивості та методи

| Метод                                    | Опис                                                                 |
|:-----------------------------------------|:---------------------------------------------------------------------|
| `getFilename(): string`                  | Повертає шлях до файлу шаблона.                                     |
| `setFilename(string $filename): self`    | Встановлює шлях до файлу шаблона.                                  |
| `getData(): array`                       | Повертає масив даних для шаблона.                                  |
| `setData(array $data): self`             | Встановлює дані для підстановки.                                  |
| `getPartial(): bool`                     | Повертає прапорець часткового рендерингу.                          |
| `setPartial(bool $partial): self`        | Встановлює режим часткового рендерингу.                           |
| `getContent(): string`                   | Повертає результат рендерингу (сформований HTML).                  |
| `setContent(string $content): self`      | Встановлює результат рендерингу.                                  |
| `__toString(): string`                   | Дозволяє виводити об’єкт як рядок HTML: `(string)$template` = content. |

## Приклад використання

### 1. Ручне завантаження та рендеринг шаблона
```php
$template = new Template(
    __DIR__ . '/views/user/profile.tmpl',
    ['user' => $userEntity],
    false
);

// Всередині рендерера:
ob_start();
extract($template->getData(), EXTR_SKIP);
include $template->getFilename();
$html = ob_get_clean();

$template->setContent($html);
echo $template; // виводить готовий HTML
```

### 2. Частковий шаблон без layout
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

echo $partial; // виводить лише HTML навігації
```

### 3. Використання в Middleware або Renderer-класі
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

[Повернутись до змісту](../../../index.md)