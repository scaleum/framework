[Back to Contents](../../../../../index.md)

**EN** | [UK](../../../../../../uk/components/storages/pdo/builders/contracts/ColumnBuilderInterface.md) | [RU](../../../../../../ru/components/storages/pdo/builders/contracts/ColumnBuilderInterface.md)
#  ColumnBuilderInterface

`ColumnBuilderInterface` defines a **fluent** (chain-calls) API for configuring columns when generating/modifying DB schema through the Scaleum Query-Builder. The interface is located in the namespace `Scaleum\Storages\PDO\Builders\Contracts` and is used by the `ColumnBuilder` builders and migrations.

##  Methods

| Signature                                                    | Return Type      | Purpose                                                                                                  |
| ------------------------------------------------------------ | ---------------- | -------------------------------------------------------------------------------------------------------- |
| `setComment(string $str): self`                              | `self`           | Sets the column comment (`COMMENT`).                                                                     |
| `getComment(): ?string`                                      | `string\|null`   | Returns the current comment.                                                                             |
| `setConstraint(mixed $constraint): self`                     | `self`           | Sets a foreign/unique/check constraint. Accepts a `Constraint` object or an SQL string.                   |
| `getConstraint(): mixed`                                     | `mixed`          | Returns the set constraint.                                                                               |
| `setDefaultValue(mixed $default, bool $quoted = true): self` | `self`           | Defines the default value (`DEFAULT`). The `$quoted` flag indicates whether to quote the value.          |
| `getDefaultValue(): mixed`                                   | `mixed`          | Current default value.                                                                                    |
| `setColumn(string $str): self`                               | `self`           | The name of the column to which changes apply.                                                          |
| `getColumn(): ?string`                                       | `string\|null`   | Returns the column name.                                                                                  |
| `setNotNull(bool $val = true): self`                         | `self`           | Enables/disables `NOT NULL`.                                                                              |
| `getNonNull(): bool`                                         | `bool`           | Is the column declared `NOT NULL`?                                                                        |
| `setUnique(bool $val = true): self`                          | `self`           | Marks the column as `UNIQUE`.                                                                             |
| `getUnique(): bool`                                          | `bool`           | Is the column unique?                                                                                      |
| `setUnsigned(bool $val = true): self`                        | `self`           | Makes a numeric column `UNSIGNED` (MySQL).                                                                |
| `getUnsigned(): bool`                                        | `bool`           | Is `UNSIGNED` set?                                                                                        |
| `setTable(string $table): self`                              | `self`           | Sets the name of the table the builder works with.                                                       |
| `setTableMode(int $mode): self`                              | `self`           | Defines the generation mode (e.g., `CREATE`, `ALTER`).                                                  |

##  Practical Tips

* **Chain calls**: all `set*()` methods return `self`, so you can configure the column compactly.
* **Value types**: `setDefaultValue()` automatically quotes strings if `$quoted = true`; for functions (`CURRENT_TIMESTAMP`), pass `false`.
* **Constraint objects**: integrate with `ConstraintBuilder` — pass a ready object to `setConstraint()`.

[Back to Contents](../../../../../index.md)
