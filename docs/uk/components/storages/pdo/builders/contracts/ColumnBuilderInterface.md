[Повернутись до змісту](../../../../../index.md)

[EN](../../../../../../en/components/storages/pdo/builders/contracts/ColumnBuilderInterface.md) | **UK** | [RU](../../../../../../ru/components/storages/pdo/builders/contracts/ColumnBuilderInterface.md)
# ColumnBuilderInterface

`ColumnBuilderInterface` визначає **флюентний** (chain‑виклики) API для конфігурування стовпців при генерації/модифікації схеми БД через Query‑Builder Scaleum. Інтерфейс розташований у просторі імен `Scaleum\Storages\PDO\Builders\Contracts` і використовується будівельниками `ColumnBuilder` та міграціями.

## Методи

| Підпис                                                        | Повертаємий тип  | Призначення |
| -------------------------------------------------------------| ---------------- | ----------- |
| `setComment(string $str): self`                              | `self`           | Встановлює коментар стовпця (`COMMENT`).                                                                 |
| `getComment(): ?string`                                      | `string\|null`   | Повертає поточний коментар.                                                                             |
| `setConstraint(mixed $constraint): self`                     | `self`           | Встановлює зовнішній/унікальний/check‑констрейнт. Приймає об’єкт `Constraint` або рядок‑SQL.             |
| `getConstraint(): mixed`                                     | `mixed`          | Повертає встановлений констрейнт.                                                                        |
| `setDefaultValue(mixed $default, bool $quoted = true): self` | `self`           | Визначає значення за замовчуванням (`DEFAULT`). Прапорець `$quoted` вказує, чи потрібно екранувати значення. |
| `getDefaultValue(): mixed`                                   | `mixed`          | Поточне значення за замовчуванням.                                                                      |
| `setColumn(string $str): self`                               | `self`           | Ім’я стовпця, до якого застосовуються зміни.                                                            |
| `getColumn(): ?string`                                       | `string\|null`   | Повертає ім’я стовпця.                                                                                   |
| `setNotNull(bool $val = true): self`                         | `self`           | Увімкнути/вимкнути `NOT NULL`.                                                                           |
| `getNonNull(): bool`                                         | `bool`           | Чи оголошено стовпець як `NOT NULL`?                                                                    |
| `setUnique(bool $val = true): self`                          | `self`           | Позначає стовпець як `UNIQUE`.                                                                           |
| `getUnique(): bool`                                          | `bool`           | Чи є стовпець унікальним?                                                                                 |
| `setUnsigned(bool $val = true): self`                        | `self`           | Робить числовий стовпець `UNSIGNED` (MySQL).                                                             |
| `getUnsigned(): bool`                                        | `bool`           | Чи встановлено `UNSIGNED`?                                                                                 |
| `setTable(string $table): self`                              | `self`           | Встановлює ім’я таблиці, з якою працює конструктор.                                                      |
| `setTableMode(int $mode): self`                              | `self`           | Визначає режим генерації (наприклад, `CREATE`, `ALTER`).                                                |

## Практичні поради

* **Chain‑виклики**: всі `set*()` повертають `self`, тому можна компактно налаштовувати стовпець.
* **Типи значень**: `setDefaultValue()` автоматично додає лапки рядкам, якщо `$quoted = true`; для функцій (`CURRENT_TIMESTAMP`) передайте `false`.
* **Constraint‑об’єкти**: інтегруйте з `ConstraintBuilder` — передавайте готовий об’єкт у `setConstraint()`.

[Повернутись до змісту](../../../../../index.md)