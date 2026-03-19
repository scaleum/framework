[Back](../template-renderer.md) | [Return to contents](../../../../index.md)

**EN** | [UK](../../../../../uk/components/http/renderers/plugins/include-template.md) | [RU](../../../../../ru/components/http/renderers/plugins/include-template.md)
#  IncludeTemplate Plugin

`IncludeTemplate` is a plugin for `TemplateRenderer` that allows including one template inside another, passing data and rendering it as a partial.

##  Purpose

- Include and render nested templates without a layout wrapper.
- Support passing data to the included template.
- Simplify composing complex views from components.

##  Interface

```php
class IncludeTemplate implements RendererPluginInterface {
    protected TemplateRenderer $renderer;

    public function getName(): string {
        return 'include';
    }

    public function register(TemplateRenderer $renderer): void {
        $this->renderer = $renderer;
    }

    public function __invoke(string $view, array $data = []): string {
        return $this->renderer->renderTemplate(
            new Template($view, $data, true)
        );
    }
}
```

###  Methods

- `getName(): string` — returns the plugin name (`include`).
- `register(TemplateRenderer $renderer): void` — stores the renderer instance.
- `__invoke(string $view, array $data = []): string` — creates a `Template` with the partial flag and calls `renderTemplate()`.

##  Usage in templates

1. **Including a simple template**
   ```twig
   {{include:partials/header.tmpl}}
   ```
   PHP equivalent:
   ```php
   echo $this->include('partials/header.tmpl');
   ```

2. **Passing data**    
   ```php
   echo $this->include('components/card.php',['title' => 'Page title', 'items' => [...]]);
   ```
   Variables `$title` and `$items` will be available in `components/card.php`.  
   Note that passing data to the "included" template is only available in `PHP notation`.

##  Code example

```php
use Scaleum\Http\Renderers\TemplateRenderer;
use Scaleum\Http\Renderers\Plugins\IncludeTemplate;

$renderer = new TemplateRenderer([
    'locations' => [__DIR__ . '/views'],
]);

echo $renderer->render('page.php', [
    'title' => 'Page',
    'sidebarItems' => ['Home','About','Contact'],
]);

// In views/page.php:
<h1>{{title}}</h1>
<?php echo $this->include('partials/sidebar.php',['items'=>$sidebarItems??[]]); ?>
<p>Content...</p>
```

When rendering `page.php`, the HTML from `views/partials/sidebar.php` will be automatically inserted, receiving the `items` data.

[Back](../template-renderer.md) | [Return to contents](../../../../index.md)