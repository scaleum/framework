[Вернуться к оглавлению](../../../../../index.md)

# ColumnBuilderInterface

`ColumnBuilderInterface` определяет **флюентный** (chain‑вызовы) API для конфигурирования столбцов при генерации/модификации схемы БД через Query‑Builder Scaleum.  Интерфейс располагается в пространстве имён `Scaleum\Storages\PDO\Builders\Contracts` и используется строителями `ColumnBuilder` и миграциями.

## Методы

| Подпись                                                      | Возвращаемый тип | Назначение |
| ------------------------------------------------------------ | ---------------- | ---------- |
| `setComment(string $str): self`                              | `self`           | Задаёт комментарий столбца (`COMMENT`).                                                                 |
| `getComment(): ?string`                                      | `string\|null`   | Возвращает текущий комментарий.                                                                         |
| `setConstraint(mixed $constraint): self`                     | `self`           | Устанавливает внешний/уникальный/чек‑констрейнт. Принимает объект `Constraint` либо строку‑SQL.         |
| `getConstraint(): mixed`                                     | `mixed`          | Возвращает установленный констрейнт.                                                                    |
| `setDefaultValue(mixed $default, bool $quoted = true): self` | `self`           | Определяет значение по умолчанию (`DEFAULT`). Флаг `$quoted` указывает, нужно ли экранировать значение. |
| `getDefaultValue(): mixed`                                   | `mixed`          | Текущее значение по умолчанию.                                                                          |
| `setColumn(string $str): self`                               | `self`           | Имя столбца, к которому применяются изменения.                                                          |
| `getColumn(): ?string`                                       | `string\|null`   | Возвращает имя столбца.                                                                                 |
| `setNotNull(bool $val = true): self`                         | `self`           | Включает/выключает `NOT NULL`.                                                                          |
| `getNonNull(): bool`                                         | `bool`           | Столбец объявлен `NOT NULL`?                                                                            |
| `setUnique(bool $val = true): self`                          | `self`           | Помечает столбец `UNIQUE`.                                                                              |
| `getUnique(): bool`                                          | `bool`           | Столбец уникален?                                                                                       |
| `setUnsigned(bool $val = true): self`                        | `self`           | Делает числовой столбец `UNSIGNED` (MySQL).                                                             |
| `getUnsigned(): bool`                                        | `bool`           | `UNSIGNED` установлен?                                                                                  |
| `setTable(string $table): self`                              | `self`           | Устанавливает имя таблицы, с которой работает конструктор.                                              |
| `setTableMode(int $mode): self`                              | `self`           | Определяет режим генерации (напр. `CREATE`, `ALTER`).                                                   |

## Практические советы

* **Chain‑вызовы**: все `set*()` возвращают `self`, поэтому можно компактно настраивать столбец.
* **Типы значений**: `setDefaultValue()` автоматически кавычит строки, если `$quoted = true`; для функций (`CURRENT_TIMESTAMP`) передайте `false`.
* **Constraint‑объекты**: интегрируйте с `ConstraintBuilder` — передавайте готовый объект в `setConstraint()`.

[Вернуться к оглавлению](../../../../../index.md)
