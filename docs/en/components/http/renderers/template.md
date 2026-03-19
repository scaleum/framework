[Back to Contents](../../../index.md)

**EN** | [UK](../../../../uk/components/http/renderers/template.md) | [RU](../../../../ru/components/http/renderers/template.md)
#  Template

`Template` is a wrapper class for working with file templates: it stores the path to the file, data for rendering, the result of the generated content, and the partial inclusion mode.

##  Purpose

- Loading and storing the template file name.
- Passing an associative array of data for substitution in the template.
- Marking the template as partial (`partial`), without a layout wrapper.
- Storing the rendering result in the `content` property.
- Converting the object to a string (`__toString`) returns the content.

##  Constructor

```php
public function __construct(
    string $filename,
    array  $data    = [],
    bool   $partial = false
)
```
- `$filename` — path to the template file (e.g., `views/user/profile.tmpl`).
- `$data` — associative array of variables available inside the template.
- `$partial` — if `true`, the template is rendered without wrappers (layout).

##  Properties and Methods

| Method                                    | Description                                                          |
|:-----------------------------------------|:---------------------------------------------------------------------|
| `getFilename(): string`                  | Returns the path to the template file.                              |
| `setFilename(string $filename): self`    | Sets the path to the template file.                                 |
| `getData(): array`                       | Returns the data array for the template.                            |
| `setData(array $data): self`             | Sets the data for substitution.                                     |
| `getPartial(): bool`                     | Returns the partial rendering flag.                                |
| `setPartial(bool $partial): self`        | Sets the partial rendering mode.                                   |
| `getContent(): string`                   | Returns the rendering result (generated HTML).                     |
| `setContent(string $content): self`      | Sets the rendering result.                                          |
| `__toString(): string`                   | Allows outputting the object as an HTML string: `(string)$template` = content. |

##  Usage Example

###  1. Manual loading and rendering of a template
```php
$template = new Template(
    __DIR__ . '/views/user/profile.tmpl',
    ['user' => $userEntity],
    false
);

// Inside the renderer:
ob_start();
extract($template->getData(), EXTR_SKIP);
include $template->getFilename();
$html = ob_get_clean();

$template->setContent($html);
echo $template; // outputs the ready HTML
```

###  2. Partial template without layout
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

echo $partial; // outputs only the navigation HTML
```

###  3. Usage in Middleware or Renderer class
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

[Back to Contents](../../../index.md)