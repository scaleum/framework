[Back](../template-renderer.md) | [Return to Contents](../../../../index.md)

**EN** | [UK](../../../../../uk/components/http/renderers/plugins/gettext.md) | [RU](../../../../../ru/components/http/renderers/plugins/gettext.md)
#  Gettext Plugin

`Gettext` is a plugin for `TemplateRenderer` that provides message translation using the `Translator` service.

##  Purpose

- Support for template localization via the syntax `{{Gettext:key|[domain]|[locale]}}`.
- Integration with the service locator to obtain an instance of `Translator`.
- Returns the original key if the translator is unavailable.

##  Interface

```php
class Gettext implements RendererPluginInterface {
    protected TemplateRenderer $renderer;

    public function getName(): string {
        return 'gettext';
    }

    public function register(TemplateRenderer $renderer): void {
        $this->renderer = $renderer;
    }

    public function __invoke(
        string $message,
        string $textDomain = 'default',
        ?string $locale    = null
    ): string {
        $translator = ServiceLocator::get('translator');
        if ($translator instanceof Translator) {
            return $translator->translate($message, $textDomain, $locale);
        }
        return $message;
    }
}
```

###  Methods

- `getName(): string` — returns the plugin name (`gettext`).
- `register(TemplateRenderer $renderer): void` — registers itself in the renderer.
- `__invoke(string $message, string $textDomain = 'default', ?string $locale = null): string` — performs the translation.

##  Usage in Templates

1. **Translation by key** (uses the default text domain and current locale):
   ```twig
   <h1>{{gettext:welcome_message}}</h1>
   <!-- or like this -->
   <h1>{{gettext:--message=welcome_message}}</h1>
   <!-- or like this if `welcome message` contains spaces -->
   <h1>{{gettext:--message="welcome message"}}</h1>
   ```
   PHP equivalent:
   ```php
   echo $translator->translate('welcome_message');
   // or
   echo $translator->translate('welcome message');
   ```

2. **Specifying the domain** (translation file):
   ```twig
   <p>{{gettext:error_not_found|errors}}</p>
   <!-- or like this -->
   <p>{{gettext:--message=error_not_found --textDomain=errors}}</p>
   ```
   where `'errors'` is the text domain name (see `Translator` settings and initialization).

3. **Forced locale**:
   ```twig
   <span>{{gettext:date_format|default|fr_FR}}</span>
   <!-- or like this -->
   <span>{{gettext:--message=date_format --locale=fr_FR --textDomain=default}}</span>
   ```
   Translates the key `date_format` for the French locale and the `default` text domain.

4. **Using quoting** (double quotes):
   ```twig
   <!-- quoting is not required -->
   <button>{{gettext:Submit}}</button>
   <button>{{gettext:Welcome, Username}}</button>
   <!-- when using named parameters, quoting strings with spaces is required -->
   <button>{{gettext:--message="Press any button"}}</button>
   ```

##  Code Examples

```php
use Scaleum\Http\Renderers\TemplateRenderer;
use Scaleum\Http\Renderers\Plugins\Gettext;
use Scaleum\i18n\Translator;
use Scaleum\Services\ServiceLocator;

// Setting up the translator in the service locator
ServiceLocator::set('translator', new Translator());

$renderer = new TemplateRenderer();
// The plugin is registered automatically in the Renderer constructor

echo $renderer->render(
    new Template(
        'views/greeting.php',
        ['name' => 'John']
    )
);

// Where greeting.php contains:
// <h2>{{gettext:hello_user}}: {{name}}</h2>
```

##  Error Handling

- If the `translator` service is [not registered](../../../service-locator.md) or does not implement `Translator` or the translation is not found, the raw key is returned.
- The plugin never throws exceptions on its own.

[Back](../template-renderer.md) | [Return to Contents](../../../../index.md)
