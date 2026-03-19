[Назад](../template-renderer.md) | [Повернутися до змісту](../../../../index.md)

[EN](../../../../../en/components/http/renderers/plugins/gettext.md) | **UK** | [RU](../../../../../ru/components/http/renderers/plugins/gettext.md)
# Плагін Gettext

`Gettext` — плагін для `TemplateRenderer`, що забезпечує переклад повідомлень за допомогою сервісу `Translator`.

## Призначення

- Підтримка локалізації шаблонів через синтаксис `{{Gettext:key|[domain]|[locale]}}`.
- Інтеграція з сервіс-локатором для отримання екземпляра `Translator`.
- Повернення оригінального ключа, якщо перекладач недоступний.

## Інтерфейс

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

### Методи

- `getName(): string` — повертає ім'я плагіна (`gettext`).
- `register(TemplateRenderer $renderer): void` — реєструє себе в рендері.
- `__invoke(string $message, string $textDomain = 'default', ?string $locale = null): string` — виконує переклад.

## Використання в шаблонах

1. **Переклад за ключем** (використовується текстовий домен за замовчуванням і поточна локаль):
   ```twig
   <h1>{{gettext:welcome_message}}</h1>
   <!-- або так -->
   <h1>{{gettext:--message=welcome_message}}</h1>
   <!-- або так, якщо `welcome message` містить пробіли -->
   <h1>{{gettext:--message="welcome message"}}</h1>
   ```
   Еквівалент PHP:
   ```php
   echo $translator->translate('welcome_message');
   // або
   echo $translator->translate('welcome message');
   ```

2. **Вказання домену** (файл перекладів):
   ```twig
   <p>{{gettext:error_not_found|errors}}</p>
   <!-- або так -->
   <p>{{gettext:--message=error_not_found --textDomain=errors}}</p>
   ```
   де `'errors'` — ім'я текстового домену (див. налаштування та ініціалізацію `Translator`).

3. **Примусова локаль**:
   ```twig
   <span>{{gettext:date_format|default|fr_FR}}</span>
   <!-- або так -->
   <span>{{gettext:--message=date_format --locale=fr_FR --textDomain=default}}</span>
   ```
   Перекладе ключ `date_format` для французької локалі та текстового домену `default`.

4. **Використання кватування** (подвійних лапок):
   ```twig
   <!-- кватування не потрібне -->
   <button>{{gettext:Submit}}</button>
   <button>{{gettext:Welcome, Username}}</button>
   <!-- при використанні іменованих параметрів потрібно кватування рядків з пробілами -->
   <button>{{gettext:--message="Press any button"}}</button>
   ```

## Приклади в коді

```php
use Scaleum\Http\Renderers\TemplateRenderer;
use Scaleum\Http\Renderers\Plugins\Gettext;
use Scaleum\i18n\Translator;
use Scaleum\Services\ServiceLocator;

// Налаштування перекладача в сервіс-локаторі
ServiceLocator::set('translator', new Translator());

$renderer = new TemplateRenderer();
// Плагін реєструється автоматично в конструкторі Renderer

echo $renderer->render(
    new Template(
        'views/greeting.php',
        ['name' => 'John']
    )
);

// Де greeting.php містить:
// <h2>{{gettext:hello_user}}: {{name}}</h2>
```

## Обробка помилок

- Якщо сервіс `translator` [не зареєстрований](../../../service-locator.md) або не реалізує `Translator` чи переклад не знайдено, повертається необроблений ключ.
- Плагін ніколи не кидає виключення самостійно.

[Назад](../template-renderer.md) | [Повернутися до змісту](../../../../index.md)