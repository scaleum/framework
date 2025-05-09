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

namespace Scaleum\Storages\PDO\Builders;

use Scaleum\Stdlib\Exceptions\EDatabaseError;
use Scaleum\Stdlib\Exceptions\EInvalidArgumentException;
use Scaleum\Stdlib\Helpers\ArrayHelper;
use Scaleum\Storages\PDO\Exceptions\ESQLError;

/**
 * QueryBuilder
 *
 * @author Maxim Kirichenko <kirichenko.maxim@gmail.com>
 */
class QueryBuilder extends BuilderAbstract implements Contracts\QueryBuilderInterface {
    protected static array $adapters = [
        'mysql'  => Adapters\MySQL\Query::class,
        'pgsql'  => Adapters\PostgreSQL\Query::class,
        'sqlite' => Adapters\SQLite\Query::class,
        'sqlsrv' => Adapters\SQLServer\Query::class,
        'mssql'  => Adapters\SQLServer\Query::class,
    ];

    protected const BRACKET_START = '(';
    protected const BRACKET_END   = ')';

    private array $cachedState      = [];
    protected array $bracketsPrev   = [];
    protected array $bracketsSource = [];
    protected array $from           = [];
    protected array $groupBy        = [];
    protected array $having         = [];
    protected array $join           = [];
    protected int $limit            = 0;
    protected array $modifiers      = [];
    protected int $offset           = 0;
    protected array $orderBy        = [];
    protected array $select         = [];
    protected array $set            = [];
    protected array $tableAliased   = [];
    protected array $where          = [];
    protected ?string $whereKey     = null;
    protected array $ctes           = [];
    protected bool $cteRecursive    = false;
    protected array $unions         = [];

    protected function cache(): self {
        $this->cachedState = get_object_vars($this);
        unset($this->cachedState['cachedState']); // Prevent recursion

        return $this;
    }

    protected function restore(): self {
        foreach ($this->cachedState as $property => $value) {
            $this->$property = $value;
        }

        return $this;
    }

    public function delete(array | string $table = null, array | string $where = null, ?int $limit = null): mixed {
        if ($table === null) {
            if (! isset($this->from[0])) {
                return false;
            }
            $table = $this->from[0];
        } elseif (is_array($table)) {
            // Every call to release() will resets the state of the object
            // So we need to cache the state of the object before each call to delete()
            $this->cache();

            $sqlBatches = [];
            foreach ($table as $tableItem) {
                // Restore the state of the object to the previous state
                $this->restore();
                $sql          = $this->delete($tableItem, $where, $limit);
                $sql          = rtrim($sql, ';') . ';';
                $sqlBatches[] = $sql;
            }

            if (count($sqlBatches) > 0) {
                return implode("\n", $sqlBatches);
            }

            throw new ESQLError('SQL query is empty');
        } else {
            $table = $this->protectIdentifiers($table);
        }

        if ($where !== null) {
            $this->where($where);
        }

        if ($limit !== null) {
            $this->limit($limit);
        }

        if (count($this->where) == 0) {
            throw new ESQLError('SQL query(delete) must use where condition');
        }

        return $this->realize($this->makeDelete($table, $this->where, $this->orderBy, $this->limit));
    }

    /**
     * Execute sql
     *
     * @param string $sql
     * @param array  $params
     * @param string $method [execute|fetch|fetchAll]
     * @param array  $args
     *
     * @return mixed
     */
    public function execute(string $sql, array $params = [], string $method = 'execute', array $args = []): mixed {
        return $this->realize($sql, $params, $method, $args);
    }

    public function flush(): self {
        parent::flush();

        $this->from           = [];
        $this->groupBy        = [];
        $this->having         = [];
        $this->join           = [];
        $this->limit          = 0;
        $this->offset         = 0;
        $this->orderBy        = [];
        $this->select         = [];
        $this->set            = [];
        $this->tableAliased   = [];
        $this->where          = [];
        $this->whereKey       = null;
        $this->bracketsSource = [];
        $this->bracketsPrev   = [];
        $this->ctes           = [];
        $this->cteRecursive   = false;
        $this->unions         = [];

        return $this;
    }

    public function from(array | string $from): self {
        $this->from = [];
        foreach ((array) $from as $val) {
            if (strpos($val, ',') !== false) {
                foreach (explode(',', $val) as $v) {
                    $v = trim($v);
                    $this->addTableAlias($v);
                    $this->from[] = $this->protectIdentifiers($v);
                }
            } else {
                $val = trim($val);

                // Extract any aliases that might exist.  We use this information
                // in the protectIdentifiers to know whether to add a table prefix
                $this->addTableAlias($val);
                $this->from[] = $this->protectIdentifiers($val);
            }
        }

        return $this;
    }

    public function groupBy(array | string $field): self {
        if (is_string($field)) {
            $field = explode(',', $field);
        }

        foreach ($field as $val) {
            $val = trim($val);

            if ($val != '') {
                $this->groupBy[] = $this->protectIdentifiers($val);
            }
        }

        return $this;
    }

    public function hasOperator($str): bool {
        $str = trim($str);
        if (! preg_match("/(\s|<|>|!|=|is null|is not null)/i", $str)) {
            return false;
        }

        return true;
    }

    public function having(array | string $field, mixed $value = null, bool $quoting = true): self {
        return $this->makeHaving($field, $value, 'AND ', $quoting);
    }

    public function havingBrackets(): self {
        return $this->brackets($this->having);
    }

    public function havingBracketsEnd(): self {
        return $this->bracketsEnd($this->having);
    }

    public function insert(?string $table = null, array $set = [], bool $replaceIfExists = false): mixed {
        if (count($set)) {
            if (ArrayHelper::isAssociative($set)) {
                $this->set($set);
            } else {
                $this->setAsBatch($set);
            }
        }

        if ($table === NULL) {
            if (! isset($this->from[0])) {
                return false;
            }
            $table = $this->from[0];
        }

        if (isset($this->set[0])) {
            $sql = $this->makeInsertBatch($this->protectIdentifiers($table), $this->set, $replaceIfExists);
        } else {
            $sql = $this->makeInsert($this->protectIdentifiers($table), array_keys($this->set), array_values($this->set), $replaceIfExists);
        }

        return $this->realize($sql);
    }

    public function join(string $table, string $rule, ?string $type = null): self {
        if ($type !== null) {
            $type = strtoupper(trim($type));

            if (! in_array($type, ['LEFT', 'RIGHT', 'OUTER', 'INNER', 'LEFT OUTER', 'RIGHT OUTER'])) {
                $type = '';
            } else {
                $type .= ' ';
            }
        } else {
            $type = '';
        }

        // Extract any aliases that might exist.  We use this information
        // in the _protect_identifiers to know whether to add a table prefix
        $this->addTableAlias($table);

        // Strip apart the condition and protect the identifiers
        if (preg_match('/([\w\.]+)([\W\s]+)(.+)/', $rule, $match)) {
            $match[1] = $this->protectIdentifiers($match[1]);
            $match[3] = $this->protectIdentifiers($match[3]);

            $rule = "$match[1]$match[2]$match[3]";
        }

        // Assemble the JOIN statement
        $join = $type . "JOIN " . $this->protectIdentifiers($table) . " ON " . $rule;

        $this->join[] = $join;

        return $this;
    }

    public function joinInner(string $table, string $rule): self {
        return $this->join($table, $rule, 'INNER');
    }

    public function joinLeft(string $table, string $rule): self {
        return $this->join($table, $rule, 'LEFT');
    }

    public function joinOuter(string $table, string $rule): self {
        return $this->join($table, $rule, 'OUTER');
    }

    public function joinRight(string $table, string $rule): self {
        return $this->join($table, $rule, 'RIGHT');
    }

    public function like(array | string $field, ?string $match = null, string $side = 'both'): mixed {
        return $this->makeLike($field, $match, 'AND ', $side);
    }

    public function limit(int $value, ?int $offset = null): self {
        $this->limit = abs($value);
        if ($offset !== null) {
            $this->offset($offset);
        }
        return $this;
    }

    public function modifiers(array | string $modifiers): self {
        if (is_string($modifiers)) {
            $modifiers = explode(' ', $modifiers);
        }

        foreach ($modifiers as $modifier) {
            $this->modifiers[] = trim(strtoupper($modifier));
        }

        return $this;
    }

    public function notLike(array | string $field, ?string $match = null, string $side = 'both'): self {
        return $this->makeLike($field, $match, 'AND ', $side, 'NOT');
    }

    public function offset(int $offset): self {
        $this->offset = abs($offset);

        return $this;
    }

    public function orHaving(array | string $field, mixed $value, bool $quoting = true): self {
        return $this->makeHaving($field, $value, 'OR ', $quoting);
    }

    public function orLike(array | string $field, ?string $match = null, string $side = 'both'): self {
        return $this->makeLike($field, $match, 'OR ', $side);
    }

    public function orNotLike(array | string $field, ?string $match = null, string $side = 'both'): self {
        return $this->makeLike($field, $match, 'OR ', $side, 'NOT');
    }
    public function orWhere(array | string $field, mixed $value = null, bool $quoting = true): self {
        return $this->makeWhere($field, $value, 'OR ', $quoting);
    }
    public function orWhereIn(string $field, array $values): self {
        return $this->makeWhereIn($field, $values, false, 'OR ');
    }

    public function orWhereNotIn(string $field, array $values): self {
        return $this->makeWhereIn($field, $values, true, 'OR ');
    }

    public function orderBy(array | string $field, array | string $direction = 'ASC'): self {
        $validDirections = ['ASC', 'DESC'];
        $orderClauses    = [];

        // Если $field массив, то $direction должен быть массивом или строкой
        if (is_array($field)) {
            foreach ($field as $index => $column) {
                $column = trim($column);
                if (in_array($column, $this->tableAliased)) {
                    continue;
                }

                // Determine the sorting direction
                $dir = strtoupper(is_array($direction) ? ($direction[$index] ?? 'ASC') : $direction);

                // Check for a valid direction value
                if (! in_array($dir, $validDirections, true)) {
                    throw new EDatabaseError(sprintf('Not allowed sorting direction: `%s`. Allowed only ASC or DESC.', $dir));
                }

                $column         = $this->protectIdentifiers($column);
                $orderClauses[] = "$column $dir";
            }
        } else {
            // If $field is a string, then direction can also be a string or an array (take the first element)
            $dir = strtoupper(is_array($direction) ? ($direction[0] ?? 'ASC') : $direction);

            // Check for a valid direction value
            if (! in_array($dir, $validDirections, true)) {
                throw new EDatabaseError(sprintf('Not allowed sorting direction: `%s`. Allowed only ASC or DESC.', $dir));
            }

            // Check for a comma separated string, then split it
            if (strpos($field, ',') !== false) {
                $columns = [];
                foreach (explode(',', $field) as $column) {
                    $column = trim($column);
                    if (in_array($column, $this->tableAliased)) {
                        continue;
                    }

                    $columns[] = $column;
                }
                return $this->orderBy($columns, $dir);
            }

            $column         = $this->protectIdentifiers($field);
            $orderClauses[] = "$column $dir";
        }

        // Add the sorting to the stack
        if (! empty($orderClauses)) {
            $this->orderBy[] = implode(', ', $orderClauses);
        }

        return $this;
    }

    public function prepare(bool $value = false): self {
        return $this->setPrepare($value);
    }

    public function optimize(bool $value = false): self {
        return $this->setOptimize($value);
    }

    public function row(array $args = []): mixed {
        return $this->realize($this->makeSelect(), [], 'fetch', $args);
    }

    public function rowColumn(array $args = []): mixed {
        return $this->realize($this->makeSelect(), [], 'fetchColumn', $args);
    }

    public function rows(array $args = []): mixed {
        return $this->realize($this->makeSelect(), [], 'fetchAll', $args);
    }

    public function select(array | string $select = '*', bool $quoting = true): self {
        if (is_string($select)) {
            $select = explode(',', $select);
        }

        $this->select = []; // overwrite/reset previous `select`, if it exists
        foreach ($select as $identifier) {
            $identifier = trim($identifier);
            if (! empty($identifier)) {
                $this->select[$identifier] = $quoting;
            }
        }

        return $this;
    }

    public function set(array | string $field, mixed $value = null, bool $quoting = true, bool $isBatch = false): self {
        // $field = $this->objectToArray($field);

        if (! is_array($field)) {
            $field = [(string) $field => $value];
        }

        if ($isBatch == true || ! ArrayHelper::isAssociative($field)) {
            $record = [];
            foreach ($field as $_key => $_value) {
                $record[$this->protectIdentifiers($_key)] = $quoting !== false ? $this->quote($_value) : $_value;
            }
            ksort($record);
            $this->set[] = $record;
        } else {
            foreach ($field as $_key => $_value) {
                $this->set[$this->protectIdentifiers($_key)] = $quoting !== false ? $this->quote($_value) : $_value;
            }
        }

        return $this;
    }

    public function setAsBatch(array $field, mixed $value = null, bool $quoting = true): self {
        if (! ArrayHelper::isAssociative($field)) {
            foreach ($field as $batch) {
                $this->setAsBatch($batch, null, $quoting);
            }
            return $this;
        }

        return $this->set($field, $value, $quoting, true);
    }

    public function truncate(string $table = null): mixed {
        if ($table === null) {
            if (! isset($this->from[0])) {
                return false;
            }

            $table = $this->from[0];
        } else {
            $table = $this->protectIdentifiers($table);
        }

        return $this->realize($this->makeTruncate($table));
    }

    public function update(array | string $tableName = null, array $set = [], array | string $where = null, ?string $whereKey = null, ?int $limit = null): mixed {
        if (count($set)) {
            if (ArrayHelper::isAssociative($set)) {
                $this->set($set);
            } else {
                $this->setAsBatch($set);
            }
        }

        if ($tableName === null) {
            if (! isset($this->from[0])) {
                return false;
            }
            $tableName = $this->from[0];
        }

        if ($where != null) {
            $this->where($where);
        }

        if ($limit != null) {
            $this->limit($limit);
        }

        if ($whereKey != null) {
            $this->whereKey($whereKey);
        }

        $sql = '';

        if (count($this->set) > 0) {
            if (ArrayHelper::isAssociative($this->set)) {
                $sql = $this->makeUpdate($this->protectIdentifiers($tableName), $this->set, $this->where, $this->orderBy, $this->limit);
            } else {
                if ($this->whereKey == null) {
                    throw new ESQLError("SQL query(update batch) must use `whereKey` condition");
                }
                $sql = $this->makeUpdateBatch($this->protectIdentifiers($tableName), $this->set, $this->where, $this->protectIdentifiers($this->whereKey), $this->orderBy, $this->limit);
            }
        }

        return $this->realize($sql);
    }

    public function where(array | string $field, mixed $value = null, bool $quoting = true): self {
        return $this->makeWhere($field, $value, 'AND ', $quoting);
    }

    public function whereBrackets(): self {
        return $this->brackets($this->where);
    }

    public function whereBracketsEnd(): self {
        return $this->bracketsEnd($this->where);
    }

    public function whereIn(string $field, array $values): self {
        return $this->makeWhereIn($field, $values);
    }

    public function whereKey(string $key): self {
        $this->whereKey = $key;

        return $this;
    }

    public function whereNotIn(string $field, array $values): self {
        return $this->makeWhereIn($field, $values, true);
    }

    protected function brackets(&$source): self {
        $source[] = self::BRACKET_START;

        $this->bracketsPrev[] = $this->bracketsSource;
        $this->bracketsSource = &$source;

        return $this;
    }

    protected function bracketsAccept($value) {
        $this->bracketsSource[] = $value;
    }

    protected function bracketsEnd(&$source): self {
        $source[] = self::BRACKET_END;

        $bracketsPrev         = array_pop($this->bracketsPrev);
        $this->bracketsSource = &$bracketsPrev;

        return $this;
    }

    protected function makeDelete(string $table, array $where = [], array $orderBy = [], ?int $limit = null): string {
        $sql = "DELETE FROM ";
        if (count($this->modifiers)) {
            $sql .= implode(" ", $this->modifiers);
        }

        $sql .= $table;

        // Write the "WHERE" portion of the query
        if (count($where) > 0) {
            $sql .= "\nWHERE ";
            $sql .= implode("\n", $this->where);
        }

        // Write the "ORDER BY" portion of the query
        if (count($orderBy) > 0) {
            $sql .= "\nORDER BY ";
            $sql .= implode(', ', $orderBy);
        }

        // Write the "LIMIT" portion of the query
        if ($limit > 0) {
            $sql .= "\n";
            $sql = $this->makeLimit($sql, $limit, 0);
        }

        return $sql;
    }

    protected function makeHaving(array | string $field, mixed $value = null, string $type = 'AND ', bool $quoting = true): self {
        if (! is_array($field)) {
            $field = [$field => $value];
        }

        foreach ($field as $k => $v) {

            switch ($this->hasBrackets()) {
            case true:
                $prefix = $type;
                if (end($this->bracketsSource) == static::BRACKET_START) {
                    $prefix = "";
                    while (end($this->bracketsSource) == static::BRACKET_START) {
                        $prefix .= array_pop($this->bracketsSource);
                    }

                    if (count($this->bracketsSource)) {
                        $prefix = "$type $prefix";
                    }
                }
                break;
            default:
                $prefix = count($this->having) == 0 ? '' : $type;
                break;
            }

            if ($quoting === true) {
                $k = $this->protectIdentifiers($k);
            }

            if (! $this->hasOperator($k)) {
                $k .= ' = ';
            }

            if ($v != '') {
                $v = ' ' . $this->quote($v);
            }

            $statement = "$prefix$k$v";

            if ($this->hasBrackets()) {
                $this->bracketsAccept($statement);
            } else {
                $this->having[] = $statement;
            }
        }

        return $this;
    }

    protected function makeInsert(string $tableName, array $keys, array $values, bool $replaceIfExists = false): string {
        $sql = 'INSERT ';
        if ($replaceIfExists == true) {
            $sql = 'REPLACE ';
        }

        if (count($this->modifiers)) {
            $sql .= implode(' ', $this->modifiers);
        }

        return "$sql INTO $tableName (" . implode(', ', $keys) . ") VALUES (" . implode(', ', $values) . ")";
    }

    protected function makeInsertBatch(string $tableName, array $rows = [], bool $replaceIfExists = false): string {
        $sql = 'INSERT ';
        if ($replaceIfExists == true) {
            $sql = 'REPLACE ';
        }

        if (count($this->modifiers)) {
            $sql .= implode(' ', $this->modifiers);
        }

        $keys   = [];
        $values = [];

        for ($index = 0; $index < count($rows); $index++) {
            if (empty($keys)) {
                $keys = array_keys($rows[$index]);
            }
            $values[] = "(" . implode(', ', array_values($rows[$index])) . ")";
        }

        return "$sql INTO $tableName (" . implode(', ', $keys) . ") VALUES " . implode(', ', $values);
    }

    protected function makeLike(array | string $field, ?string $match = null, string $type = 'AND ', string $side = 'both', string $not = ''): self {
        if (! is_array($field)) {
            $field = [$field => $match];
        }

        foreach ($field as $key => $value) {
            switch ($this->hasBrackets()) {
            case true:
                $prefix = $type;
                if (end($this->bracketsSource) == static::BRACKET_START) {
                    $prefix = "";
                    while (end($this->bracketsSource) == static::BRACKET_START) {
                        $prefix .= array_pop($this->bracketsSource);
                    }

                    if (count($this->bracketsSource)) {
                        $prefix = "$type $prefix";
                    }
                }
                break;
            default:
                $prefix = count($this->where) > 0 ? $type : '';
                break;
            }

            $statement = match (strtolower($side)) {
                'none' => "$prefix {$this->protectIdentifiers($key)} $not LIKE {$this->quote($value)}",
                'before' => "$prefix {$this->protectIdentifiers($key)} $not LIKE {$this->quote("%$value")}",
                'after' => "$prefix {$this->protectIdentifiers($key)} $not LIKE {$this->quote("$value%")}",
                default => "$prefix {$this->protectIdentifiers($key)} $not LIKE {$this->quote("%$value%")}",
            };

            if ($this->hasBrackets()) {
                $this->bracketsAccept($statement);
            } else {
                $this->where[] = $statement;
            }
        }

        return $this;
    }

    protected function makeLimit(string $sql, int $limit, int $offset): string {
        $queryParts = [];
        if ($limit > 0) {
            $queryParts[] = "LIMIT $limit";
        }

        if ($offset > 0) {
            $queryParts[] = "OFFSET $offset";
        }

        if (! empty($queryParts)) {
            $sql .= "\n" . implode(' ', $queryParts);
        }

        return $sql;
    }

    protected function makeSelect(): string {
        $sql = $this->makeWith();
        $sql .= "\nSELECT ";
        if (count($this->modifiers)) {
            $sql .= implode(' ', $this->modifiers) . ' ';
        }

        if (count($this->select) == 0) {
            $sql .= '*';
        } else {
            // Cycle through the "select" portion of the query and prep each column name.
            // The reason we protect identifiers here rather then in the select() function
            // is because until the user calls the from() function we don't know if there are aliases
            foreach ($this->select as $key => $quoting) {
                $this->select[$key] = $this->protectIdentifiers($key, $quoting);
            }

            $sql .= implode(', ', $this->select);
        }

        // Write the "FROM" portion of the query
        if (count($this->from) > 0) {
            // fix for MySQL 8.x
            // Note! when using nested queries in FROM (...),
            // the presence of isolating brackets is controlled by the developer
            $br_start = self::BRACKET_START;
            $br_end   = self::BRACKET_END;
            $from     = implode(', ', $this->from);

            if (!preg_match('/\s*(SELECT|FROM|JOIN)\b/i', $from)) {
                $br_start = $br_end = '';
            }

            $sql .= "\nFROM ";
            $sql .= "$br_start$from$br_end";
        }

        // Write the "JOIN" portion of the query
        if (count($this->join) > 0) {
            $sql .= "\n";
            $sql .= implode("\n", $this->join);
        }

        // Write the "WHERE" portion of the query
        if (count($this->where) > 0) {
            $sql .= "\nWHERE ";
            $sql .= implode("\n", $this->where);
        }

        // Write the "GROUP BY" portion of the query
        if (count($this->groupBy) > 0) {
            $sql .= "\nGROUP BY ";
            $sql .= implode(', ', $this->groupBy);
        }

        // Write the "HAVING" portion of the query
        if (count($this->having) > 0) {
            $sql .= "\nHAVING ";
            $sql .= implode("\n", $this->having);
        }

        // Write the "ORDER BY" portion of the query
        if (count($this->orderBy) > 0) {
            $sql .= "\nORDER BY ";
            $sql .= implode(', ', $this->orderBy);
        }

        // Write the "LIMIT" & "OFFSET" portion of the query
        if ($this->limit > 0 || $this->offset > 0) {
            $sql .= "\n";
            $sql = $this->makeLimit($sql, $this->limit, $this->offset);
        }

        // Write the UNION
        foreach ($this->unions as $union) {
            $sql .= $union['all']
            ? "\n UNION ALL " . $union['sql']
            : "\n UNION " . $union['sql'];
        }

        return $sql;
    }

    protected function makeTruncate(string $tableName): string {
        return "TRUNCATE $tableName";
    }

    protected function makeUpdate(array | string $tableName, array $values = [], array $where = [], array $orderBy = [], int $limit = 0): string {
        $sql = 'UPDATE ';
        if (count($this->modifiers)) {
            $sql .= implode(' ', $this->modifiers) . ' ';
        }

        $values_str = [];
        foreach ($values as $key => $val) {
            $values_str[] = "$key = $val";
        }

        $tableName = is_array($tableName) ? implode(", ", $tableName) : $tableName;
        $sql .= $tableName . " SET " . implode(', ', $values_str);

        // Write the "WHERE" portion of the query
        if (count($where) > 0) {
            $sql .= "\nWHERE ";
            $sql .= implode("\n", $where);
        }

        // Write the "ORDER BY" portion of the query
        if (count($orderBy) > 0) {
            $sql .= "\nORDER BY ";
            $sql .= implode(', ', $orderBy);
        }

        // Write the "LIMIT" portion of the query
        if ($limit > 0) {
            $sql .= "\n";
            $sql = $this->makeLimit($sql, $limit, 0);
        }

        return $sql;
    }

    protected function makeUpdateBatch(array | string $tableName, array $values = [], array $where = [], ?string $whereKey = null, array $orderBy = [], ?int $limit = 0): string {
        if (empty($whereKey)) {
            return '';
        }

        $sql = "UPDATE ";
        if (count($this->modifiers)) {
            $sql .= implode(' ', $this->modifiers) . ' ';
        }

        $tableName = is_array($tableName) ? implode(", ", $tableName) : $tableName;
        $cases_idx = [];
        $cases     = [];
        foreach ($values as $value) {
            $cases_idx[] = $value[$whereKey];
            foreach (array_keys($value) as $field) {
                if ($field != $whereKey) {
                    $cases[$field][] = "WHEN " . $whereKey . " = " . $value[$whereKey] . " THEN " . $value[$field];
                }
            }
        }

        $sql .= "$tableName SET ";

        $cases_str = '';
        foreach ($cases as $k => $v) {
            $cases_str .= "$k = CASE \n";
            foreach ($v as $row) {
                $cases_str .= "$row\n";
            }
            $cases_str .= "ELSE $k END, ";
        }

        $sql .= substr($cases_str, 0, -2);

        // Write the "WHERE" portion of the query
        if (count($where) > 0) {
            $sql .= "\nWHERE ";
            $sql .= implode("\n", $where) . ' AND ';
        }

        $sql .= $whereKey . " IN (" . implode(',', $cases_idx) . ")";

        // Write the "ORDER BY" portion of the query
        if (count($orderBy) > 0) {
            $sql .= "\nORDER BY ";
            $sql .= implode(', ', $orderBy);
        }

        // Write the "LIMIT" portion of the query
        if ($limit > 0) {
            $sql .= "\n";
            $sql = $this->makeLimit($sql, $limit, 0);
        }

        return $sql;
    }

    protected function makeWhere(array | string $field, mixed $value = null, string $type = 'AND ', bool $quoting = true): self {
        if (! is_array($field)) {
            $field = [$field => $value];
        }

        foreach ($field as $key => $value) {
            switch ($this->hasBrackets()) {
            case true:
                $prefix = $type;
                if (end($this->bracketsSource) == static::BRACKET_START) {
                    $prefix = "";
                    while (end($this->bracketsSource) == static::BRACKET_START) {
                        $prefix .= array_pop($this->bracketsSource);
                    }

                    if (count($this->bracketsSource)) {
                        $prefix = "$type $prefix";
                    }
                }
                break;
            default:
                $prefix = count($this->where) > 0 ? $type : '';
                break;
            }

            if ($value === NULL && ! $this->hasOperator($key)) {
                // value appears not to have been set, assign the test to IS NULL
                $key .= " IS NULL";
            }

            if ($value !== NULL) {
                $key = $this->protectIdentifiers($key);
                if ($quoting === true) {
                    // $key = $this->protectIdentifiers($key, $quoting);
                    $value = ' ' . $this->quote($value);
                }

                if (! $this->hasOperator($key)) {
                    $key .= " = ";
                }
            } else {
                $key = $this->protectIdentifiers($key, $quoting);
            }

            $statement = "$prefix$key$value";

            if ($this->hasBrackets()) {
                $this->bracketsAccept($statement);
            } else {
                $this->where[] = $statement;
            }
        }

        return $this;
    }

    protected function makeWhereIn(string $field, array $values, $not = false, $type = 'AND '): self {
        $not     = $not ? ' NOT' : '';
        $whereIn = [];

        foreach ($values as $value) {
            $whereIn[] = $this->quote($value);
        }

        $whereIn = implode(", ", $whereIn);

        if (! empty($whereIn)) {
            switch ($this->hasBrackets()) {
            case true:
                $prefix = $type;
                if (end($this->bracketsSource) == static::BRACKET_START) {
                    $prefix = "";
                    while (end($this->bracketsSource) == static::BRACKET_START) {
                        $prefix .= array_pop($this->bracketsSource);
                    }

                    if (count($this->bracketsSource)) {
                        $prefix = "$type $prefix";
                    }
                }
                break;
            default:
                $prefix = count($this->where) > 0 ? $type : '';
                break;
            }

            $statement = $prefix . $this->protectIdentifiers($field) . $not . " IN (" . $whereIn . ") ";

            if ($this->hasBrackets()) {
                $this->bracketsAccept($statement);
            } else {
                $this->where[] = $statement;
            }
        }

        return $this;
    }

    /**
     * @return bool
     */
    protected function hasBrackets() {
        return count($this->bracketsSource) > 0;
    }

    protected function addTableAlias(array | string $table): bool {
        if (is_array($table)) {
            foreach ($table as $tableItem) {
                $this->addTableAlias($tableItem);
            }

            return true;
        }

        // Does the string contain a comma?  If so, we need to separate
        // the string into discreet statements
        if (strpos($table, ',') !== false) {
            return $this->addTableAlias(explode(',', $table));
        }

        // if a table alias is used we can recognize it by a space
        if (strpos($table, " ") !== false) {
            // if the alias is written with the AS keyword, remove it
            $table = preg_replace('/\s+AS\s+/i', ' ', $table);

            // Grab the alias
            $table = trim(strrchr($table, " "));

            // Store the alias, if it doesn't already exist
            if (! in_array($table, $this->tableAliased)) {
                $this->tableAliased[] = $table;
            }

            return true;
        }

        return false;
    }

    public function whereBetween(string $field, array $range): self {
        return $this->makeWhereBetween($field, $range, false, 'AND ');
    }

    public function orWhereBetween(string $field, array $range): self {
        return $this->makeWhereBetween($field, $range, false, 'OR ');
    }

    public function whereNotBetween(string $field, array $range): self {
        return $this->makeWhereBetween($field, $range, true, 'AND ');
    }

    public function orWhereNotBetween(string $field, array $range): self {
        return $this->makeWhereBetween($field, $range, true, 'OR ');
    }

    protected function makeWhereBetween(string $field, array $range, bool $not = false, string $type = 'AND '): self {
        if (count($range) !== 2) {
            throw new EInvalidArgumentException('The range must contain exactly two values.');
        }

        [$start, $end] = $range;
        $notSql        = $not ? ' NOT' : '';

        if ($this->hasBrackets()) {
            $prefix = $type;
            if (end($this->bracketsSource) === static::BRACKET_START) {
                $prefix = '';
                while (end($this->bracketsSource) === static::BRACKET_START) {
                    $prefix .= array_pop($this->bracketsSource);
                }
                if (count($this->bracketsSource)) {
                    $prefix = "$type $prefix";
                }
            }
        } else {
            $prefix = count($this->where) > 0 ? $type : '';
        }

        $fieldQuoted = $this->protectIdentifiers($field);
        $startQuoted = $this->quote($start);
        $endQuoted   = $this->quote($end);
        $statement   = "{$prefix}{$fieldQuoted}{$notSql} BETWEEN {$startQuoted} AND {$endQuoted}";

        if ($this->hasBrackets()) {
            $this->bracketsAccept($statement);
        } else {
            $this->where[] = $statement;
        }

        return $this;
    }

    public function with(string $alias, string $sql, array $columns = []): self {
        // $this->ctes[$alias] = $sql;
        $this->ctes[] = [
            'alias'     => $alias,
            'query'     => $sql,
            'columns'   => $columns,
            'recursive' => false,
        ];
        return $this;
    }

    public function withRecursive(string $alias, string $sql,array $columns = []): self {
        // $this->cteRecursive = true;
        // return $this->with($alias, $sql);
        $this->ctes[] = [
            'alias'     => $alias,
            'query'     => $sql,
            'columns'   => $columns,
            'recursive' => true,
        ];
        return $this;
    }

    protected function makeWith(): string {
        if (empty($this->ctes)) {
            return '';
        }

        $useRecursive = false;
        foreach ($this->ctes as $cte) {
            if ($cte['recursive']) {
                $useRecursive = true;
                break;
            }
        }

        $keyword = $useRecursive ? 'WITH RECURSIVE' : 'WITH';
        $parts   = [];

        foreach ($this->ctes as $cte) {
            // экранируем алиас
            $alias = $this->protectIdentifiers($cte['alias']);
            // если заданы колонки — формируем список в скобках
            $cols = '';
            if (! empty($cte['columns'])) {
                $quoted = array_map(
                    fn(string $col) => $this->protectIdentifiers($col),
                    $cte['columns']
                );
                $cols = ' (' . implode(', ', $quoted) . ')';
            }
            $parts[] = "$alias$cols AS ({$cte['query']})";
        }

        return $keyword . ' ' . implode(', ', $parts) . ' ';
    }

    public function union(callable $callback): self {
        return $this->addUnion($callback, false);
    }

    public function unionAll(callable $callback): self {
        return $this->addUnion($callback, true);
    }

    protected function addUnion(callable $callback, bool $all): self {
        // create a new instance of the query builder
        $query = new static($this->getDatabase());
        $callback($query);

        // building the SQL of the subquery and saving it
        $this->unions[] = [
            'sql' => $query->makeSelect(),
            'all' => $all,
        ];

        return $this;
    }

    public function whereNull(string $field): self {
        return $this->makeWhereNull($field, false, 'AND ');
    }

    public function orWhereNull(string $field): self {
        return $this->makeWhereNull($field, false, 'OR ');
    }

    public function whereNotNull(string $field): self {
        return $this->makeWhereNull($field, true, 'AND ');
    }

    public function orWhereNotNull(string $field): self {
        return $this->makeWhereNull($field, true, 'OR ');
    }

    protected function makeWhereNull(string $field, bool $not, string $type = 'AND '): self {
        if ($this->hasBrackets()) {
            $prefix = $type;
            if (end($this->bracketsSource) === static::BRACKET_START) {
                $prefix = '';
                while (end($this->bracketsSource) === static::BRACKET_START) {
                    $prefix .= array_pop($this->bracketsSource);
                }
                if (count($this->bracketsSource)) {
                    $prefix = "$type$prefix";
                }
            }
        } else {
            $prefix = count($this->where) > 0 ? $type : '';
        }

        // build expression
        $fieldQuoted = $this->protectIdentifiers($field);
        $notSql      = $not ? ' NOT' : '';
        $statement   = "{$prefix}{$fieldQuoted} IS{$notSql} NULL";

        // add to where or brackets logic
        if ($this->hasBrackets()) {
            $this->bracketsAccept($statement);
        } else {
            $this->where[] = $statement;
        }

        return $this;
    }
}
/** End of QueryBuilder **/