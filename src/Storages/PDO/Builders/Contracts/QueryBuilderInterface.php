<?php
declare (strict_types = 1);
/**
 * This file is part of Scaleum Framework.
 *
 * (C) 2009-2025 Maxim Kirichenko <kirichenko.maxim@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Scaleum\Storages\PDO\Builders\Contracts;

/**
 * QueryBuilderInterface
 *
 * @author Maxim Kirichenko <kirichenko.maxim@gmail.com>
 */
interface QueryBuilderInterface {
    public function delete(array | string $table = null, array | string $where = null, ?int $limit = null): mixed;
    public function execute(string $sql, array $params = [], string $method = 'execute', array $args = []): mixed;
    public function flush(): self;
    public function from(array | string $from): self;
    public function groupBy(array | string $field): self;
    public function having(array | string $field, mixed $value = null, bool $quoting = true): self;
    public function insert(?string $table = null, array $set = [], bool $replaceIfExists = false): mixed;
    public function join(string $table, string $rule, ?string $type = null): self;
    public function joinInner(string $table, string $rule): self;
    public function joinLeft(string $table, string $rule): self;
    public function joinOuter(string $table, string $rule): self;
    public function joinRight(string $table, string $rule): self;
    public function like(string $field, ?string $match = null, string $side = 'both'): mixed;
    public function limit(int $value): self;
    public function modifiers(array | string $modifiers): self;
    public function notLike(string $field, ?string $match = null, string $side = 'both'): self;
    public function offset(int $offset): self;
    public function orHaving(array | string $field, mixed $value, bool $quoting = true): self;
    public function orLike(string $field, ?string $match = null, string $side = 'both'): self;
    public function orNotLike(string $field, ?string $match = null, string $side = 'both'): self;
    public function orWhere(array | string $field, mixed $value = null, bool $quoting = true): self;
    public function orWhereIn(string $field, array $values): self;
    public function orWhereNotIn(string $field, array $values): self;
    public function orderBy(array | string $field, array | string $direction = 'ASC'): self;
    public function prepare(bool $value = false): self;
    public function optimize(bool $value = false): self;
    public function row(array $args = []): mixed;
    public function rowColumn(array $args = []): mixed;
    public function rows(array $args = []): mixed;
    public function select(array | string $select = '*', bool $quoting = true): self;
    public function set(array | string $field, mixed $value = null, bool $quoting = true, bool $isBatch = false): self;
    public function setAsBatch(array $field, mixed $value = null, bool $quoting = true): self;
    public function truncate(?string $table = null): mixed;
    public function update(?string $table = null, array $set = [], array | string $where = null, ?string $whereKey = null, ?int $limit = null): mixed;
    public function where(array | string $field, mixed $value = null, bool $quoting = true): self;
    public function whereBrackets(): self;
    public function whereBracketsEnd(): self;
    public function whereIn(string $field, array $values): self;
    public function whereNotIn(string $field, array $values): self;
    public function whereKey(string $key): self;
    public function whereNull(string $field): self;
    public function whereNotNull(string $field): self;
    public function orWhereNull(string $field): self;
    public function orWhereNotNull(string $field): self;
    public function whereBetween(string $field, array $range): self;
    public function orWhereBetween(string $field, array $range): self;
    public function whereNotBetween(string $field, array $range): self;
    public function orWhereNotBetween(string $field, array $range): self;
    public function with(string $alias, string $sql,array $columns = []): self;
    public function withRecursive(string $alias, string $sql,array $columns = []): self;
    public function union(callable $callback): self;
    public function unionAll(callable $callback): self;
}
/** End of QueryBuilderInterface **/