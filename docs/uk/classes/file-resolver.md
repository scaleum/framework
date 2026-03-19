[Повернутись до змісту](../index.md)

[EN](../../en/classes/file-resolver.md) | **UK** | [RU](../../ru/classes/file-resolver.md)
# FileResolver

**FileResolver** — клас для пошуку та розв’язання файлів за зареєстрованими шляхами.

## Призначення

- Розв’язання відносних шляхів файлів
- Пошук файлів за списком базових директорій
- Динамічне додавання та видалення шляхів пошуку

## Основні можливості

| Метод | Призначення |
|:------|:------------|
| `resolve(string $filename)` | Пошук файлу в зареєстрованих шляхах |
| `addPath(string\|array $str, bool $unshift = true)` | Додавання шляху або списку шляхів |
| `getPaths()` | Отримання всіх зареєстрованих шляхів |
| `setPaths(array $array)` | Встановлення списку шляхів |
| `deletePath(string\|array $str)` | Видалення шляху або списку шляхів |

## Приклади використання

### Пошук файлу

```php
$resolver = new FileResolver();
$resolver->addPath('/var/www/project/config');

$file = $resolver->resolve('app.php');
if ($file !== false) {
    echo "Файл знайдено: $file";
}
```

### Додавання кількох шляхів

```php
$resolver->addPath(['/var/www/project/config', '/var/www/shared/config']);
```

### Видалення шляху

```php
$resolver->deletePath('/var/www/project/config');
```

### Встановлення списку шляхів напряму

```php
$resolver->setPaths(['/var/www/project/config', '/var/www/shared/config']);
```

## Винятки

- Окремі винятки не викидаються; результат пошуку — `false`, якщо файл не знайдено.

[Повернутись до змісту](../index.md)