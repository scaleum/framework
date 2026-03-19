[Назад](../template-renderer.md) | [Повернутися до змісту](../../../../index.md)

[EN](../../../../../en/components/http/renderers/plugins/renderer-plugin-interface.md) | **UK** | [RU](../../../../../ru/components/http/renderers/plugins/renderer-plugin-interface.md)
# Інтерфейс RendererPluginInterface

`RendererPluginInterface` — контракт для всіх плагінів рендерера шаблонів у фреймворку Scaleum. Визначає методи для ідентифікації та реєстрації плагіна в `TemplateRenderer`.

## Призначення

- Забезпечити єдиний механізм пошуку та виклику плагінів всередині `TemplateRenderer`.
- Гарантувати, що кожен плагін знає своє ім'я (`getName()`) і здатен зареєструватися в рендері (`register()`).

## Визначення інтерфейсу

```php
interface RendererPluginInterface
{
    /**
     * Повертає унікальне ім'я плагіна для використання в синтаксисі шаблону
     *
     * @return string Ім'я плагіна (наприклад, 'include', 'asset', 'gettext')
     */
    public function getName(): string;

    /**
     * Реєструє плагін у рендері, передає собі екземпляр `TemplateRenderer`
     *
     * @param TemplateRenderer $renderer Екземпляр рендерера шаблонів
     */
    public function register(TemplateRenderer $renderer): void;
}
```

## Методи

| Метод                                        | Опис                                                            |
| -------------------------------------------- | --------------------------------------------------------------- |
| `getName(): string`                          | Повертає ім'я плагіна, під яким він викликається в шаблонах.    |
| `register(TemplateRenderer $renderer): void` | Зберігає посилання на рендерер і виконує ініціалізацію плагіна. |

## Приклад реалізації плагіна

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
        // Можна зареєструвати хуки або додати в конфіг рендерера
    }

    public function __invoke(string $param): string {
        // Логіка плагіна, повертає рядок
        return strtoupper($param);
    }
}
```

## Використання плагіна в шаблонах

1. **Реєстрація плагіна** (зазвичай автоматично в конструкторі рендерера або вручну):
   ```php
   $renderer->registerPlugin(new CustomPlugin());
   ```
2. **Виклик із шаблону**:
   ```twig
   {{custom:hello world}}  → виведе 'HELLO WORLD'
   ```

[Назад](../template-renderer.md) | [Повернутися до змісту](../../../../index.md)