[Вернуться к оглавлению](../../../index.md)
# TemplateRenderer

`TemplateRenderer` — мощный класс для рендеринга шаблонов во фреймворке Scaleum. Позволяет:

- Управлять директориями поиска шаблонов (`locations`) и алиасами (`views`).
- Подключать плагины для расширения синтаксиса: включение других шаблонов, ассетов, переводов.
- Автоматически оборачивать контент в layout или рендерить частичные шаблоны.

## Конструктор и базовая настройка

```php
public function __construct(array $config = [])
```
- Принимает конфиг для `Hydrator`: `locations`, `views`, `layout`, `plugins`, `timeout` и т.п.
- Устанавливает плагины по умолчанию: `IncludeTemplate`, `IncludeAsset`, `Gettext`.
- Рекомендуется добавить директории поиска шаблонов:
  ```php
  $renderer->setLocations([
      __DIR__ . '/views',
      __DIR__ . '/templates',
  ]);
  ```

## Основные методы

### render(string|Template \$view, array \$data = [], bool \$partial = false): string
- Принимает имя файла шаблона (или экземпляр `Template`), данные и флаг частичного рендеринга.
- Возвращает готовый HTML с учётом layout, если `partial=false`.

### renderPartial(string \$view, array \$data = []): string
- Удобный псевдоним для `render(..., true)` — рендер без обёртки layout.

### Управление локациями и алиасами
```php
$renderer
    ->addLocation(__DIR__ . '/views/users')   // приоритетная папка
    ->addLocation(__DIR__ . '/views/common', false);

$renderer->addView('home', __DIR__ . '/views/main/home.php');
```
- `resolveTemplate()` ищет файл сначала в `views`, затем в `locations`.

### Плагины
- Плагины реализуют `RendererPluginInterface` и регистрируются в конструкторе или вручную:
  ```php
  $renderer->registerPlugin(new MyCustomPlugin());
  ```
- Синтаксис в шаблонах:
  - `{{pluginName:val1|val2}}` массив значений, которые будут переданы в `__invoke` плагина в той же последовательности, в которой указаны;
  - `{{pluginName}}content{{/pluginName}}` строка `content` как один параметр который будут передан в `__invoke` плагина;

## Примеры использования

### 1. Рендер простого шаблона с layout
```php
// config.php
$renderer = new TemplateRenderer([
    'locations' => [__DIR__ . '/views'],
    'layout'    => 'layout/main',
]);

echo $renderer->render('home', ['title' => 'Главная']);
// Загрузит views/home.php, вставит в views/layout/main.php
```

### 2. Частичный шаблон без layout
```php
echo $renderer->renderPartial('partials/navbar', ['active'=>'about']);
```

### 3. Включение другого шаблона через плагин
```php
// В views/page.php:
// <h1>{{title}}</h1>
// {{include:sidebar.php}}            — включит sidebar.php

$renderer->render('page', ['title'=>'О нас']);
```

### 4. Включение CSS/JS ассетов
```php
// В шаблоне header.php:
// {{asset:/css/style.css}}  — сгенерирует <link rel="stylesheet" href="/css/style.css">
// {{asset:/js/app.js}}      — сгенерирует <script src="/js/app.js"></script>

$renderer->renderPartial('header');
```

### 5. Перевод через Gettext-плагин
```php
// В шаблоне:
// <p>{{gettext:welcome_message}}</p>
// Под капотом вызовет Gettext::translate('welcome_message')

echo $renderer->render('welcome');
```

### 6. Пользовательский layout
```php
$renderer->setLayout('layouts/admin');
// Теперь вызовы render() будут оборачивать контент views/... в views/layouts/admin.php
```

## Обработка ошибок

- `ENotFoundError` — шаблон не найден в указанных локациях.
- `EInOutException` — ошибка при включении файла шаблона.
- `ERuntimeError` — общие ошибки рендеринга.

[Вернуться к оглавлению](../../../index.md)