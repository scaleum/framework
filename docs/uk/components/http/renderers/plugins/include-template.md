[Назад](../template-renderer.md) | [Повернутися до змісту](../../../../index.md)

[EN](../../../../../en/components/http/renderers/plugins/include-template.md) | **UK** | [RU](../../../../../ru/components/http/renderers/plugins/include-template.md)
# Плагін IncludeTemplate

`IncludeTemplate` — плагін для `TemplateRenderer`, що дозволяє включати один шаблон всередину іншого, передаючи дані та рендерячи його як частковий.

## Призначення

- Включати та рендерити вкладені шаблони без обгортки layout.
- Підтримувати передачу даних у включений шаблон.
- Спрощувати складання складних представлень із компонентів.

## Інтерфейс

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

### Методи

- `getName(): string` — повертає ім'я плагіна (`include`).
- `register(TemplateRenderer $renderer): void` — зберігає екземпляр рендерера.
- `__invoke(string $view, array $data = []): string` — створює `Template` з прапорцем partial і викликає `renderTemplate()`.

## Використання в шаблонах

1. **Включення простого шаблону**
   ```twig
   {{include:partials/header.tmpl}}
   ```
   Еквівалент PHP:
   ```php
   echo $this->include('partials/header.tmpl');
   ```

2. **Передача даних**    
   ```php
   echo $this->include('components/card.php',['title' => 'Page title', 'items' => [...]]);
   ```
   У `components/card.php` будуть доступні змінні `$title` і `$items`.  
   Зверніть увагу, що передача даних у "підключений" шаблон доступна лише в `PHP-нотації`.

## Приклад у коді

```php
use Scaleum\Http\Renderers\TemplateRenderer;
use Scaleum\Http\Renderers\Plugins\IncludeTemplate;

$renderer = new TemplateRenderer([
    'locations' => [__DIR__ . '/views'],
]);

echo $renderer->render('page.php', [
    'title' => 'Сторінка',
    'sidebarItems' => ['Home','About','Contact'],
]);

// У views/page.php:
<h1>{{title}}</h1>
<?php echo $this->include('partials/sidebar.php',['items'=>$sidebarItems??[]]); ?>
<p>Content...</p>
```

Під час рендерингу `page.php` буде автоматично вставлено HTML з `views/partials/sidebar.php`, отримавши дані `items`.

[Назад](../template-renderer.md) | [Повернутися до змісту](../../../../index.md)