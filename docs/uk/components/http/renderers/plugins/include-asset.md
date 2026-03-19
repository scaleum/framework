[Назад](../template-renderer.md) | [Повернутися до змісту](../../../../index.md)

[EN](../../../../../en/components/http/renderers/plugins/include-asset.md) | **UK** | [RU](../../../../../ru/components/http/renderers/plugins/include-asset.md)
# Плагін IncludeAsset

`IncludeAsset` — плагін для `TemplateRenderer`, який відповідає за генерацію URL до статичних ресурсів (CSS, JS, зображень) з автоматичним додаванням параметра версії на основі часу останньої модифікації файлу.

## Призначення

- Полегшити підключення ассетів у шаблонах через синтаксис `{{asset:path/to/file}}`.
- Автоматично додавати `?v=<timestamp>`, щоб при оновленні файлів обходити кеш браузера.
- Враховувати кореневу директорію веб-сервера (`DOCUMENT_ROOT`).

## Інтерфейс

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
        // Приводить відносний шлях до повного імені файлу
        $filename = FileHelper::prepFilename(
            PathHelper::join(
                $_SERVER['DOCUMENT_ROOT'] ?? '/',
                $path
            ),
            true
        );
        // Якщо файл існує, бере час модифікації, інакше — поточний timestamp
        $version  = file_exists($filename)
            ? filemtime($filename)
            : time();
        // Повертає URL з параметром версії
        return "{$path}?v={$version}";
    }
}
```

### Методи

- `getName(): string` — повертає ім'я плагіна (`asset`).
- `register(TemplateRenderer $renderer): void` — реєструє екземпляр рендерера.
- `__invoke(string $path): string` — приймає відносний URL ресурсу і повертає URL з версійним параметром.

## Використання у шаблонах

1. **Підключення CSS**
   ```html
   <link rel="stylesheet" href="{{asset:/css/style.css}}">
   ```
   Результат:
   ```html
   <link rel="stylesheet" href="/css/style.css?v=1617890123">
   ```

2. **Підключення JS**
   ```html
   <script src="{{asset:/js/app.js}}"></script>
   ```
   Результат:
   ```html
   <script src="/js/app.js?v=1617890456"></script>
   ```

3. **Підключення зображення**
   ```html
   <img src="{{asset:/images/logo.png}}" alt="Logo">
   ```
   Результат:
   ```html
   <img src="/images/logo.png?v=1617890789" alt="Logo">
   ```

## Приклад у коді

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

У шаблоні `header.php` можна використовувати:
```php
<link href="<?= \$this->asset('/css/style.css') ?>" rel="stylesheet">
<script src="<?= \$this->asset('/js/app.js') ?>"></script>
```

[Назад](../template-renderer.md) | [Повернутися до змісту](../../../../index.md)