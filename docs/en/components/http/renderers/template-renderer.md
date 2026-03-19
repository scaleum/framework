[Back to Contents](../../../index.md)

**EN** | [UK](../../../../uk/components/http/renderers/template-renderer.md) | [RU](../../../../ru/components/http/renderers/template-renderer.md)
#  TemplateRenderer

`TemplateRenderer` is a powerful class for rendering templates in the Scaleum framework. It allows you to:

- Manage template search directories (`locations`) and aliases (`views`).
- Connect plugins to extend syntax: including other templates, assets, translations.
- Automatically wrap content in a layout or render partial templates.

##  Constructor and Basic Setup

```php
public function __construct(array $config = [])
```
- Accepts a config for `Hydrator`: `locations`, `views`, `layout`, `plugins`, `timeout`, etc.
- Sets default plugins: `IncludeTemplate`, `IncludeAsset`, `Gettext`.
- It is recommended to add template search directories:
  ```php
  $renderer->setLocations([
      __DIR__ . '/views',
      __DIR__ . '/templates',
  ]);
  ```

##  Main Methods

###  render(string|Template $view, array $data = [], bool $partial = false): string
- Accepts a template file name (or a `Template` instance), data, and a partial rendering flag.
- Returns the final HTML considering the layout if `partial=false`.

###  renderPartial(string $view, array $data = []): string
- A convenient alias for `render(..., true)` — rendering without wrapping in a layout.

###  Managing Locations and Aliases
```php
$renderer
    ->addLocation(__DIR__ . '/views/users')   // priority folder
    ->addLocation(__DIR__ . '/views/common', false);

$renderer->addView('home', __DIR__ . '/views/main/home.php');
```
- `resolveTemplate()` searches files first in `views`, then in `locations`.

###  Plugins
- Plugins implement `RendererPluginInterface` and are registered in the constructor or manually:
  ```php
  $renderer->registerPlugin(new MyCustomPlugin());
  ```
- Syntax in templates:
  - `{{pluginName:val1|val2}}` an array of values passed to the plugin's `__invoke` in the order specified;
  - `{{pluginName:--var1=val1 --var2=val2 --var3="Sometext with spaces"}}` an associative key/value array passed to the plugin's `__invoke` according to parameter names; use double quotes for string values with spaces;
  - `{{pluginName}}content{{/pluginName}}` the `content` value as a single parameter (`[content]`) passed to the plugin's `__invoke`;
  - `{{pluginName:val1|val2}}content{{/pluginName}}` the `content` value as the last parameter (`[val1,val2,content]`) passed to the plugin's `__invoke`;

##  Usage Examples

###  1. Rendering a Simple Template with Layout
```php
// config.php
$renderer = new TemplateRenderer([
    'locations' => [__DIR__ . '/views'],
    'layout'    => 'layout/main',
]);

echo $renderer->render('home', ['title' => 'Home']);
// Loads views/home.php, inserts into views/layout/main.php
```

###  2. Partial Template without Layout
```php
echo $renderer->renderPartial('partials/navbar', ['active'=>'about']);
```

###  3. Including Another Template via Plugin
```php
// In views/page.php:
// <h1>{{title}}</h1>
// {{include:sidebar.php}}            — includes sidebar.php

$renderer->render('page', ['title'=>'About Us']);
```

###  4. Including CSS/JS Assets
```php
// In header.php template:
// {{asset:/css/style.css}}  — generates <link rel="stylesheet" href="/css/style.css">
// {{asset:/js/app.js}}      — generates <script src="/js/app.js"></script>

$renderer->renderPartial('header');
```

###  5. Translation via Gettext Plugin
```php
// In template:
// <p>{{gettext:welcome_message}}</p>
// Under the hood calls Gettext::translate('welcome_message')

echo $renderer->render('welcome');
```

###  6. Custom Layout
```php
$renderer->setLayout('layouts/admin');
// Now render() calls will wrap views/... content in views/layouts/admin.php
```

##  Error Handling

- `ENotFoundError` — template not found in specified locations.
- `EInOutException` — error including the template file.
- `ERuntimeError` — general rendering errors.

[Back to Contents](../../../index.md)