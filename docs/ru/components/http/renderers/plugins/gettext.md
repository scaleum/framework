[Назад](../template-renderer.md) | [Вернуться к оглавлению](../../../../index.md)
# Плагин Gettext

`Gettext` — плагин для `TemplateRenderer`, обеспечивающий перевод сообщений с помощью сервиса `Translator`.

## Назначение

- Поддержка локализации шаблонов через синтаксис `{{Gettext:key|[domain]|[locale]}}`.
- Интеграция с сервис-локатором для получения экземпляра `Translator`.
- Возврат оригинального ключа, если переводчик недоступен.

## Интерфейс

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

### Методы

- `getName(): string` — возвращает имя плагина (`gettext`).
- `register(TemplateRenderer $renderer): void` — регистрирует себя в рендерере.
- `__invoke(string $message, string $textDomain = 'default', ?string $locale = null): string` — выполняет перевод.

## Использование в шаблонах

1. **Перевод по ключу** (используется текстовый домен по умолчанию и текущая локаль):
   ```twig
   <h1>{{gettext:welcome_message}}</h1>
   <!-- или так -->
   <h1>{{gettext:--message=welcome_message}}</h1>
   <!-- или так, если `welcome message` содержит пробелы -->
   <h1>{{gettext:--message="welcome message"}}</h1>
   ```
   Эквивалент PHP:
   ```php
   echo $translator->translate('welcome_message');
   // или
   echo $translator->translate('welcome message');
   ```

2. **Указание домена** (файл переводов):
   ```twig
   <p>{{gettext:error_not_found|errors}}</p>
   <!-- или так -->
   <p>{{gettext:--message=error_not_found --textDomain=errors}}</p>
   ```
   где `'errors'` — имя текстового домена (см. настройки и инициализацию `Translator`).

3. **Принудительная локаль**:
   ```twig
   <span>{{gettext:date_format|default|fr_FR}}</span>
   <!-- или так -->
   <span>{{gettext:--message=date_format --locale=fr_FR --textDomain=default}}</span>
   ```
   Переведёт ключ `date_format` для французской локали и текстового домена `default`.

4. **Использование квотирования** (двойных кавычек):
   ```twig
   <!-- квотирование не нужно -->
   <button>{{gettext:Submit}}</button>
   <button>{{gettext:Welcome, Username}}</button>
   <!-- при использовании разименованных параметров нужно квотирование строк с пробелами -->
   <button>{{gettext:--message="Press any button"}}</button>
   ```

## Примеры в коде

```php
use Scaleum\Http\Renderers\TemplateRenderer;
use Scaleum\Http\Renderers\Plugins\Gettext;
use Scaleum\i18n\Translator;
use Scaleum\Services\ServiceLocator;

// Настройка переводчика в сервис-локаторе
ServiceLocator::set('translator', new Translator());

$renderer = new TemplateRenderer();
// Плагин регистрируется автоматически в конструкторе Renderer

echo $renderer->render(
    new Template(
        'views/greeting.php',
        ['name' => 'John']
    )
);

// Где greeting.php содержит:
// <h2>{{gettext:hello_user}}: {{name}}</h2>
```

## Обработка ошибок

- Если сервис `translator` [не зарегистрирован](../../../service-locator.md) или не реализует `Translator` или перевод не найден, возвращается необработанный ключ.
- Плагин никогда не бросает исключения самостоятельно.

[Назад](../template-renderer.md) | [Вернуться к оглавлению](../../../../index.md)
