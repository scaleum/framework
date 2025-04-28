[Назад](../template-renderer.md) | [Вернуться к оглавлению](../../../../index.md)
# Плагин IncludeAsset

`IncludeAsset` — плагин для `TemplateRenderer`, отвечающий за генерацию URL к статическим ресурсам (CSS, JS, изображениям) с автоматическим добавлением параметра версии на основе времени последней модификации файла.

## Назначение

- Облегчить подключение ассетов в шаблонах через синтаксис `{{asset:path/to/file}}`.
- Автоматически добавлять `?v=<timestamp>`, чтобы при обновлении файлов обходить кэш браузера.
- Учитывать корневую директорию веб-сервера (`DOCUMENT_ROOT`).

## Интерфейс

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
        // Приводит относительный путь к полному файловому имени
        $filename = FileHelper::prepFilename(
            PathHelper::join(
                $_SERVER['DOCUMENT_ROOT'] ?? '/',
                $path
            ),
            true
        );
        // Если файл существует, берёт время модификации, иначе — текущий timestamp
        $version  = file_exists($filename)
            ? filemtime($filename)
            : time();
        // Возвращает URL с параметром версии
        return "{$path}?v={$version}";
    }
}
```

### Методы

- `getName(): string` — возвращает имя плагина (`asset`).
- `register(TemplateRenderer $renderer): void` — регистрирует экземпляр рендерера.
- `__invoke(string $path): string` — принимает относительный URL ресурса и возвращает URL с версионным параметром.

## Использование в шаблонах

1. **Подключение CSS**
   ```html
   <link rel="stylesheet" href="{{asset:/css/style.css}}">
   ```
   Результат:
   ```html
   <link rel="stylesheet" href="/css/style.css?v=1617890123">
   ```

2. **Подключение JS**
   ```html
   <script src="{{asset:/js/app.js}}"></script>
   ```
   Результат:
   ```html
   <script src="/js/app.js?v=1617890456"></script>
   ```

3. **Подключение изображения**
   ```html
   <img src="{{asset:/images/logo.png}}" alt="Logo">
   ```
   Результат:
   ```html
   <img src="/images/logo.png?v=1617890789" alt="Logo">
   ```

## Пример в коде

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

В шаблоне `header.php` можно использовать:
```php
<link href="<?= \$this->asset('/css/style.css') ?>" rel="stylesheet">
<script src="<?= \$this->asset('/js/app.js') ?>"></script>
```

[Назад](../template-renderer.md) | [Вернуться к оглавлению](../../../../index.md)