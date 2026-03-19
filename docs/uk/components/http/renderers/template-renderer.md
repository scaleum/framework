[Повернутись до змісту](../../../index.md)

[EN](../../../../en/components/http/renderers/template-renderer.md) | **UK** | [RU](../../../../ru/components/http/renderers/template-renderer.md)
# TemplateRenderer

`TemplateRenderer` — потужний клас для рендерингу шаблонів у фреймворку Scaleum. Дозволяє:

- Керувати директоріями пошуку шаблонів (`locations`) та псевдонімами (`views`).
- Підключати плагіни для розширення синтаксису: включення інших шаблонів, ассетів, перекладів.
- Автоматично обгортати контент у layout або рендерити часткові шаблони.

## Конструктор і базове налаштування

```php
public function __construct(array $config = [])
```
- Приймає конфіг для `Hydrator`: `locations`, `views`, `layout`, `plugins`, `timeout` тощо.
- Встановлює плагіни за замовчуванням: `IncludeTemplate`, `IncludeAsset`, `Gettext`.
- Рекомендується додати директорії пошуку шаблонів:
  ```php
  $renderer->setLocations([
      __DIR__ . '/views',
      __DIR__ . '/templates',
  ]);
  ```

## Основні методи

### render(string|Template \$view, array \$data = [], bool \$partial = false): string
- Приймає ім'я файлу шаблону (або екземпляр `Template`), дані та прапорець часткового рендерингу.
- Повертає готовий HTML з урахуванням layout, якщо `partial=false`.

### renderPartial(string \$view, array \$data = []): string
- Зручний псевдонім для `render(..., true)` — рендер без обгортки layout.

### Керування локаціями та псевдонімами
```php
$renderer
    ->addLocation(__DIR__ . '/views/users')   // пріоритетна папка
    ->addLocation(__DIR__ . '/views/common', false);

$renderer->addView('home', __DIR__ . '/views/main/home.php');
```
- `resolveTemplate()` шукає файл спочатку в `views`, потім у `locations`.

### Плагіни
- Плагіни реалізують `RendererPluginInterface` і реєструються в конструкторі або вручну:
  ```php
  $renderer->registerPlugin(new MyCustomPlugin());
  ```
- Синтаксис у шаблонах:
  - `{{pluginName:val1|val2}}` масив значень, які будуть передані в `__invoke` плагіна у тій же послідовності, в якій вказані;
  - `{{pluginName:--var1=val1 --var2=val2 --var3="Sometext with spaces"}}` асоціативний масив ключ/значення, які будуть передані в `__invoke` плагіна відповідно до імен (параметрів); для рядкових значень із пробілами використовуйте подвійні лапки;
  - `{{pluginName}}content{{/pluginName}}` значення `content` як єдиний параметр(`[content]`), який буде переданий в `__invoke` плагіна;
  - `{{pluginName:val1|val2}}content{{/pluginName}}` значення `content` як останній параметр(`[val1,val2,content]`), який буде переданий в `__invoke` плагіна;

## Приклади використання

### 1. Рендер простого шаблону з layout
```php
// config.php
$renderer = new TemplateRenderer([
    'locations' => [__DIR__ . '/views'],
    'layout'    => 'layout/main',
]);

echo $renderer->render('home', ['title' => 'Головна']);
// Завантажить views/home.php, вставить у views/layout/main.php
```

### 2. Частковий шаблон без layout
```php
echo $renderer->renderPartial('partials/navbar', ['active'=>'about']);
```

### 3. Включення іншого шаблону через плагін
```php
// У views/page.php:
// <h1>{{title}}</h1>
// {{include:sidebar.php}}            — включить sidebar.php

$renderer->render('page', ['title'=>'Про нас']);
```

### 4. Включення CSS/JS ассетів
```php
// У шаблоні header.php:
// {{asset:/css/style.css}}  — згенерує <link rel="stylesheet" href="/css/style.css">
// {{asset:/js/app.js}}      — згенерує <script src="/js/app.js"></script>

$renderer->renderPartial('header');
```

### 5. Переклад через Gettext-плагін
```php
// У шаблоні:
// <p>{{gettext:welcome_message}}</p>
// Під капотом викличе Gettext::translate('welcome_message')

echo $renderer->render('welcome');
```

### 6. Користувацький layout
```php
$renderer->setLayout('layouts/admin');
// Тепер виклики render() будуть обгортати контент views/... у views/layouts/admin.php
```

## Обробка помилок

- `ENotFoundError` — шаблон не знайдено у вказаних локаціях.
- `EInOutException` — помилка при включенні файлу шаблону.
- `ERuntimeError` — загальні помилки рендерингу.

[Повернутись до змісту](../../../index.md)