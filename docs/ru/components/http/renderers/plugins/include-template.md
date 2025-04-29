[Назад](../template-renderer.md) | [Вернуться к оглавлению](../../../../index.md)
# Плагин IncludeTemplate

`IncludeTemplate` — плагин для `TemplateRenderer`, позволяющий включать один шаблон внутрь другого, передавая данные и рендеря его как частичный.

## Назначение

- Включать и рендерить вложенные шаблоны без обёртки layout.
- Поддерживать передачу данных в включаемый шаблон.
- Упрощать составление сложных представлений из компонент.

## Интерфейс

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

### Методы

- `getName(): string` — возвращает имя плагина (`include`).
- `register(TemplateRenderer $renderer): void` — сохраняет экземпляр рендерера.
- `__invoke(string $view, array $data = []): string` — создаёт `Template` с флагом partial и вызывает `renderTemplate()`.

## Использование в шаблонах

1. **Включение простого шаблона**
   ```twig
   {{include:partials/header.tmpl}}
   ```
   Эквивалент PHP:
   ```php
   echo $this->include('partials/header.tmpl');
   ```

2. **Передача данных**    
   ```php
   echo $this->include('components/card.php',['title' => 'Page title', 'items' => [...]]);
   ```
   В `components/card.php` будут доступны переменные `$title` и `$items`.  
   Обратите внимание, что передача данных в "подключаемый" шаблон доступна только в `PHP-нотации`.

## Пример в коде

```php
use Scaleum\Http\Renderers\TemplateRenderer;
use Scaleum\Http\Renderers\Plugins\IncludeTemplate;

$renderer = new TemplateRenderer([
    'locations' => [__DIR__ . '/views'],
]);

echo $renderer->render('page.php', [
    'title' => 'Страница',
    'sidebarItems' => ['Home','About','Contact'],
]);

// В views/page.php:
<h1>{{title}}</h1>
<?php echo $this->include('partials/sidebar.php',['items'=>$sidebarItems??[]]); ?>
<p>Content...</p>
```

При рендере `page.php` будет автоматически вставлен HTML из `views/partials/sidebar.php`, получив данные `items`.

[Назад](../template-renderer.md) | [Вернуться к оглавлению](../../../../index.md)