[Назад](../template-renderer.md) | [Вернуться к оглавлению](../../../../index.md)
# Интерфейс RendererPluginInterface

`RendererPluginInterface` — контракт для всех плагинов рендерера шаблонов во фреймворке Scaleum. Определяет методы для идентификации и регистрации плагина в `TemplateRenderer`.

## Назначение

- Обеспечить единый механизм поиска и вызова плагинов внутри `TemplateRenderer`.
- Гарантировать, что каждый плагин знает своё имя (`getName()`) и способен зарегистрироваться в рендерере (`register()`).

## Определение интерфейса

```php
interface RendererPluginInterface
{
    /**
     * Возвращает уникальное имя плагина для использования в синтаксисе шаблона
     *
     * @return string Имя плагина (например, 'include', 'asset', 'gettext')
     */
    public function getName(): string;

    /**
     * Регистрирует плагин в рендерере, передаёт себе экземпляр `TemplateRenderer`
     *
     * @param TemplateRenderer $renderer Экземпляр рендерера шаблонов
     */
    public function register(TemplateRenderer $renderer): void;
}
```

## Методы

| Метод                                        | Описание                                                        |
| -------------------------------------------- | --------------------------------------------------------------- |
| `getName(): string`                          | Возвращает имя плагина, под которым он вызывается в шаблонах.   |
| `register(TemplateRenderer $renderer): void` | Сохраняет ссылку на рендерер и выполняет инициализацию плагина. |

## Пример реализации плагина

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
        // Можно зарегистрировать хуки или добавить в конфиг рендерера
    }

    public function __invoke(string $param): string {
        // Логика плагина, возвращает строку
        return strtoupper($param);
    }
}
```

## Использование плагина в шаблонах

1. **Регистрация плагина** (обычно автоматически в конструкторе рендерера или вручную):
   ```php
   $renderer->registerPlugin(new CustomPlugin());
   ```
2. **Вызов из шаблона**:
   ```twig
   {{custom:hello world}}  → выведет 'HELLO WORLD'
   ```

[Назад](../template-renderer.md) | [Вернуться к оглавлению](../../../../index.md)