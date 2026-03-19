[Back](../template-renderer.md) | [Return to contents](../../../../index.md)

**EN** | [UK](../../../../../uk/components/http/renderers/plugins/renderer-plugin-interface.md) | [RU](../../../../../ru/components/http/renderers/plugins/renderer-plugin-interface.md)
#  RendererPluginInterface Interface

`RendererPluginInterface` is a contract for all template renderer plugins in the Scaleum framework. It defines methods for identifying and registering a plugin in the `TemplateRenderer`.

##  Purpose

- Provide a unified mechanism for searching and invoking plugins within `TemplateRenderer`.
- Ensure that each plugin knows its name (`getName()`) and can register itself in the renderer (`register()`).

##  Interface Definition

```php
interface RendererPluginInterface
{
    /**
     * Returns the unique plugin name for use in template syntax
     *
     * @return string Plugin name (e.g., 'include', 'asset', 'gettext')
     */
    public function getName(): string;

    /**
     * Registers the plugin in the renderer, passing the `TemplateRenderer` instance
     *
     * @param TemplateRenderer $renderer Template renderer instance
     */
    public function register(TemplateRenderer $renderer): void;
}
```

##  Methods

| Method                                        | Description                                                     |
| -------------------------------------------- | --------------------------------------------------------------- |
| `getName(): string`                          | Returns the plugin name under which it is called in templates.  |
| `register(TemplateRenderer $renderer): void` | Stores a reference to the renderer and performs plugin initialization. |

##  Plugin Implementation Example

```php
use Scaleum\Http\Renderers\TemplateRenderer;
use Scaleum\Http\Renderers\Plugins\RendererPluginInterface;

class CustomPlugin implements RendererPluginInterface {
    protected TemplateRenderer $renderer;

    public function getName(): string {
        return 'custom';
    }

    public function register(TemplateRenderer $renderer): void {
        $this->renderer = $renderer;
        // You can register hooks or add to the renderer config here
    }

    public function __invoke(string $param): string {
        // Plugin logic, returns a string
        return strtoupper($param);
    }
}
```

##  Using the Plugin in Templates

1. **Register the plugin** (usually automatically in the renderer constructor or manually):
   ```php
   $renderer->registerPlugin(new CustomPlugin());
   ```
2. **Call from template**:
   ```twig
   {{custom:hello world}}  → outputs 'HELLO WORLD'
   ```

[Back](../template-renderer.md) | [Return to contents](../../../../index.md)