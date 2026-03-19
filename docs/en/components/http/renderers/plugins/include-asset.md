[Back](../template-renderer.md) | [Return to Contents](../../../../index.md)

**EN** | [UK](../../../../../uk/components/http/renderers/plugins/include-asset.md) | [RU](../../../../../ru/components/http/renderers/plugins/include-asset.md)
#  IncludeAsset Plugin

`IncludeAsset` is a plugin for `TemplateRenderer` responsible for generating URLs to static resources (CSS, JS, images) with automatic addition of a version parameter based on the file's last modification time.

##  Purpose

- Facilitate asset inclusion in templates using the syntax `{{asset:path/to/file}}`.
- Automatically add `?v=<timestamp>` to bypass browser cache when files are updated.
- Take into account the web server's root directory (`DOCUMENT_ROOT`).

##  Interface

```php
class IncludeAsset implements RendererPluginInterface {
    protected TemplateRenderer $renderer;

    public function getName(): string {
        return 'asset';
    }

    public function register(TemplateRenderer $renderer): void {
        $this->renderer = $renderer;
    }

    public function __invoke(string $path): string {
        // Converts relative path to full filename
        $filename = FileHelper::prepFilename(
            PathHelper::join(
                $_SERVER['DOCUMENT_ROOT'] ?? '/',
                $path
            ),
            true
        );
        // If the file exists, get modification time, otherwise current timestamp
        $version  = file_exists($filename)
            ? filemtime($filename)
            : time();
        // Returns URL with version parameter
        return "{$path}?v={$version}";
    }
}
```

###  Methods

- `getName(): string` — returns the plugin name (`asset`).
- `register(TemplateRenderer $renderer): void` — registers the renderer instance.
- `__invoke(string $path): string` — accepts a relative resource URL and returns the URL with a version parameter.

##  Usage in Templates

1. **Including CSS**
   ```html
   <link rel="stylesheet" href="{{asset:/css/style.css}}">
   ```
   Result:
   ```html
   <link rel="stylesheet" href="/css/style.css?v=1617890123">
   ```

2. **Including JS**
   ```html
   <script src="{{asset:/js/app.js}}"></script>
   ```
   Result:
   ```html
   <script src="/js/app.js?v=1617890456"></script>
   ```

3. **Including an Image**
   ```html
   <img src="{{asset:/images/logo.png}}" alt="Logo">
   ```
   Result:
   ```html
   <img src="/images/logo.png?v=1617890789" alt="Logo">
   ```

##  Code Example

```php
use Scaleum\Http\Renderers\TemplateRenderer;

$renderer = new TemplateRenderer([
    'locations' => [__DIR__ . '/views'],
]);

echo $renderer->render('header', [
    'cssPath' => '{{asset:/css/style.css}}',
    'jsPath'  => '{{asset:/js/app.js}}',
]);
```

In the `header.php` template you can use:
```php
<link href="<?= \$this->asset('/css/style.css') ?>" rel="stylesheet">
<script src="<?= \$this->asset('/js/app.js') ?>"></script>
```

[Back](../template-renderer.md) | [Return to Contents](../../../../index.md)