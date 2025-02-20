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
use Scaleum\Storages\PDO\Builders\Contracts\ColumnBuilderInterface;
use Scaleum\Storages\PDO\Builders\Contracts\IndexBuilderInterface;
use Scaleum\Storages\PDO\Builders\Contracts\SchemaBuilderInterface;

/**
 * SchemaBuilder
 *
 * @author Maxim Kirichenko <kirichenko.maxim@gmail.com>
 */
class SchemaBuilder extends BuilderAbstract implements SchemaBuilderInterface {
    protected static array $adapters = [
        'mysql'  => Adapters\MySQL\Schema::class,
        'pgsql'  => Adapters\PostgreSQL\Schema::class,
        'sqlite' => Adapters\SQLite\Schema::class,
        'sqlsrv' => Adapters\SQLServer\Schema::class,
        'oci'    => Adapters\Oracle\Schema::class,
    ];

    protected array $columns = [];
    protected array $keys    = [];

    public function addColumn($column): mixed {
        return $this->createColumn($column);
    }

    public function addIndex($key): mixed {
        return $this->createIndex($key);
    }

    public function columnBigInt(?int $length = null): ColumnBuilderInterface {
        return $this->createColumnBuilder(ColumnBuilder::TYPE_BIGINT, $length);
    }

    public function columnBigPk(?int $length = null): ColumnBuilderInterface {
        return $this->createColumnBuilder(ColumnBuilder::TYPE_BIGPK, $length);
    }

    public function columnBinary(?int $length = null): ColumnBuilderInterface {
        return $this->createColumnBuilder(ColumnBuilder::TYPE_BINARY, $length);
    }

    public function columnBoolean(): ColumnBuilderInterface {
        return $this->createColumnBuilder(ColumnBuilder::TYPE_BOOLEAN);
    }

    public function columnDate(): ColumnBuilderInterface {
        return $this->createColumnBuilder(ColumnBuilder::TYPE_DATE);
    }

    public function columnDateTime(): ColumnBuilderInterface {
        return $this->createColumnBuilder(ColumnBuilder::TYPE_DATETIME);
    }

    public function columnDecimal(mixed $precision = null): ColumnBuilderInterface {
        return $this->createColumnBuilder(ColumnBuilder::TYPE_DECIMAL, $precision);
    }

    public function columnDouble(mixed $precision = null): ColumnBuilderInterface {
        return $this->createColumnBuilder(ColumnBuilder::TYPE_DOUBLE, $precision);
    }

    public function columnFloat(mixed $precision = null): ColumnBuilderInterface {
        return $this->createColumnBuilder(ColumnBuilder::TYPE_FLOAT, $precision);
    }

    public function columnInt(?int $length = null): ColumnBuilderInterface {
        return $this->createColumnBuilder(ColumnBuilder::TYPE_INTEGER, $length);
    }

    public function columnJson(): ColumnBuilderInterface {
        return $this->createColumnBuilder(ColumnBuilder::TYPE_JSON);
    }

    public function columnLongText(): ColumnBuilderInterface {
        return $this->createColumnBuilder(ColumnBuilder::TYPE_LONG_TEXT);
    }

    public function columnMediumText(): ColumnBuilderInterface {
        return $this->createColumnBuilder(ColumnBuilder::TYPE_MEDIUM_TEXT);
    }

    public function columnMoney(mixed $precision = null): ColumnBuilderInterface {
        return $this->createColumnBuilder(ColumnBuilder::TYPE_MONEY, $precision);
    }

    public function columnPrimaryKey(?int $length = null): ColumnBuilderInterface {
        return $this->createColumnBuilder(ColumnBuilder::TYPE_PK, $length);
    }

    public function columnSmallint(?int $length = null): ColumnBuilderInterface {
        return $this->createColumnBuilder(ColumnBuilder::TYPE_SMALLINT, min(5, $length));
    }

    public function columnString(?int $length = null): ColumnBuilderInterface {
        return $this->createColumnBuilder(ColumnBuilder::TYPE_STRING, $length);
    }

    public function columnText(): ColumnBuilderInterface {
        return $this->createColumnBuilder(ColumnBuilder::TYPE_TEXT);
    }

    public function columnTime(mixed $precision = null): ColumnBuilderInterface {
        return $this->createColumnBuilder(ColumnBuilder::TYPE_TIME, $precision);
    }

    public function columnTimestamp(mixed $precision = null): ColumnBuilderInterface {
        return $this->createColumnBuilder(ColumnBuilder::TYPE_TIMESTAMP, $precision);
    }

    public function columnTinyint(?int $length = null): ColumnBuilderInterface {
        return $this->createColumnBuilder(ColumnBuilder::TYPE_TINYINT, min(3, $length));
    }

    public function createColumn(array | ColumnBuilderInterface $column, ?string $tableName = null): mixed {
        if ($column instanceof ColumnBuilderInterface) {
            $this->columns[] = $column;
        } elseif (is_array($column)) {
            $this->columns = array_merge($this->columns, $column);
        }

        if (empty($tableName)) {
            return $this;
        }

        $sql = $this->makeAlterTableAddColumns($tableName, $this->columns);

        return $this->execute($sql);
    }

    public function createDatabase(string $database, bool $ifNotExists = false, ?string $charSet = null, ?string $collate = null): mixed {
        $sql = $this->makeCreateDatabase($database, $ifNotExists, $charSet, $collate);

        return $this->execute($sql);
    }

    public function createIndex(array | IndexBuilderInterface $key, ?string $tableName = null): mixed {
        if ($key instanceof IndexBuilderInterface) {
            $this->keys[] = $key;
        } elseif (is_array($key)) {
            $this->keys = array_merge($this->keys, $key);
        }

        if (empty($tableName)) {
            return $this;
        }

        $sql = $this->makeCreateTableIndexes($tableName, $this->keys);
        return $this->execute($sql);
    }

    public function createTable(string $tableName, bool $ifNotExists = false): mixed {
        if (empty($tableName)) {
            throw new EDatabaseError('A table name is required for that operation.');
        }

        if (count($this->columns) == 0) {
            throw new EDatabaseError('Field information is required.');
        }

        $sql = $this->makeCreateTable($tableName, $this->columns, $this->keys, $ifNotExists);

        return $this->execute($sql);
    }

    public function showTable(string $tableName): mixed {
        $sql = $this->makeShowTable($tableName);

        return $this->realize($sql, [], 'fetchAll');
    }

    public function dropColumn(array | string $column, string $tableName): mixed {
        if (is_string($column)) {
            $this->columns[] = $column;
        } elseif (is_array($column)) {
            $this->columns = array_merge($this->columns, $column);
        }

        $sql = $this->makeAlterTableDropColumns($tableName, $this->columns);

        return $this->execute($sql);
    }

    public function dropDatabase(string $database, bool $ifExists = false): mixed {
        $sql = $this->makeDropDatabase($database, $ifExists);

        return $this->execute($sql);
    }

    public function dropIndex(string $indexName, string $tableName): mixed {
        $sql = $this->makeDropIndex($indexName, $tableName);

        return $this->execute($sql);
    }

    public function dropPrimaryKey(string $indexName, string $tableName): mixed {
        $sql = $this->makeDropIndex($indexName, $tableName, true);

        return $this->execute($sql);
    }

    public function dropTable(string $tableName, bool $ifExists = false): mixed {
        $sql = $this->makeDropTable($tableName, $ifExists);

        return $this->execute($sql);
    }

    public function existsTable(string $tableName): bool {
        $sql = $this->makeExistsTable($tableName);

        return $this->execute($sql) > 0;
    }

    public function index(array | string $column, ?string $indexName = null): IndexBuilderInterface {
        return $this->createIndexBuilder(IndexBuilder::TYPE_INDEX, $column, $indexName);
    }

    public function indexFulltext(array | string $column, ?string $indexName = null): IndexBuilderInterface {
        return $this->createIndexBuilder(IndexBuilder::TYPE_FULLTEXT, $column, $indexName);
    }

    public function indexUnique(array | string $column, ?string $indexName = null): IndexBuilderInterface {
        return $this->createIndexBuilder(IndexBuilder::TYPE_UNIQUE, $column, $indexName);
    }

    public function indexForeign(array | string $column, ?string $indexName = null): IndexBuilderInterface {
        return $this->createIndexBuilder(IndexBuilder::TYPE_FOREIGN, $column, $indexName);
    }

    public function modifyColumn(mixed $column, string $tableName): mixed {
        if (is_string($column)) {
            if (strpos($column, ' ') === false) {
                throw new EDatabaseError('Column information is required for that operation.');
            }
            $this->columns[$column] = [];
        } elseif ($column instanceof ColumnBuilderInterface) {
            $this->columns[] = $column;
        } elseif (is_array($column)) {
            $this->columns = array_merge($this->columns, $column);
        }

        $sql = $this->makeAlterTableModifyColumns($tableName, $this->columns);

        return $this->execute($sql);
    }

    public function prepare(bool $value = false): self {
        return $this->setPrepare($value);
    }

    public function optimize(bool $value = false): self {
        return $this->setOptimize($value);
    }

    public function primaryKey(mixed $column): IndexBuilderInterface {
        return $this->createIndexBuilder(IndexBuilder::TYPE_PK, $column);
    }

    public function renameTable(string $fromTable, string $toTable): mixed {
        $sql = $this->makeAlterTableName($fromTable, $toTable);

        return $this->execute($sql);
    }

    public function showDatabases(): mixed {
        $sql = $this->makeShowDatabases();

        return $this->realize($sql, [], 'fetchAll');
    }

    public function showIndex(string $tableName, ?string $database = null): mixed {
        $sql = $this->makeShowIndexes($tableName, $database);

        return $this->realize($sql, [], 'fetchAll');
    }

    public function showTables(?string $database = null): mixed {
        $sql = $this->makeShowTables($database);

        return $this->realize($sql, [], 'fetchAll');
    }

    protected function makeAlterTableAddColumns(string $tableName, array $columns = []): string {
        return $this->makeAlterTableColumns('ADD', $tableName, $columns);
    }

    protected function makeAlterTableModifyColumns(string $tableName, array $columns = []): string {
        return $this->makeAlterTableColumns('MODIFY', $tableName, $columns);
    }

    protected function makeAlterTableDropColumns(string $tableName, array $columns = []): string {
        return $this->makeAlterTableColumns('DROP', $tableName, $columns);
    }

    protected function makeAlterTableColumns(string $alterSpec, string $tableName, array $columns = []): string {
        $alterSpec = strtoupper($alterSpec);
        $sql       = sprintf('ALTER TABLE %s %s;', $this->protectIdentifiers($tableName), $this->makeColumns($columns, $alterSpec));

        return $sql;
    }

    protected function makeAlterTableName(string $fromTable, string $toTable): string {
        $sql = sprintf('ALTER TABLE %s RENAME TO %s;', $this->protectIdentifiers($fromTable), $this->protectIdentifiers($toTable));

        return $sql;
    }

    protected function makeCreateTableIndexes(string $tableName, array $indexes = []): string {
        foreach ($indexes as $index) {
            if ($index instanceof IndexBuilderInterface) {
                $index->table($tableName);
            }
        }

        return $this->makeIndexes($indexes, ";") . ";";
    }

    protected function makeColumns(array $columns, ?string $alterSpec = null) {
        $columns_count = 0;
        $sql           = '';

        foreach ($columns as $column) {
            if ($column instanceof ColumnBuilderInterface) {
                $column = (string) $column;
            }

            $sql .= "\n\t$alterSpec ";
            $sql .= $this->protectIdentifiers($column);

            // don't add a comma on the end of the last field
            if (++$columns_count < count($columns)) {
                $sql .= ',';
            }
        }

        return $sql;
    }

    protected function makeCreateDatabase(string $database, bool $ifNotExists = true, ?string $charSet = null, ?string $collate = null): string {
        if (empty($database)) {
            throw new EDatabaseError('Database name can not be empty.');
        }

        $sql = 'CREATE DATABASE ';

        if ($ifNotExists == true) {
            $sql .= 'IF NOT EXISTS ';
        }

        $sql .= $this->quoteIdentifier($database);

        if (! empty($charSet)) {
            $sql .= ' CHARACTER SET ' . $charSet;
        }

        if (! empty($collate)) {
            $sql .= ' COLLATE ' . $collate;
        }

        return $sql . ';';
    }

    protected function makeCreateTable(string $tableName, array $columns, array $keys, bool $ifNotExists = true): string {
        $result = 'CREATE TABLE ';

        if ($ifNotExists === true) {
            $result .= 'IF NOT EXISTS ';
        }

        $result .= $this->quoteIdentifier($tableName) . " (" . $this->makeColumns($columns) . (! empty($indexes = $this->makeIndexes($keys)) ? ", $indexes" : "") . ");";
        return $result;
    }

    protected function makeShowTable(string $tableName): string {
        return "DESCRIBE $tableName";
    }

    protected function makeDropDatabase(string $database, bool $ifExists = true): string {
        if (empty($database)) {
            throw new EDatabaseError('Database name can not be empty.');
        }

        $sql = 'DROP DATABASE ';
        if ($ifExists == true) {
            $sql .= 'IF EXISTS ';
        }
        $sql .= $this->quoteIdentifier($database) . ';';

        return $sql;
    }

    protected function makeDropIndex(string $indexName, string $tableName, bool $isPrimaryKey = false): string {
        if ($isPrimaryKey == true) {
            return 'ALTER TABLE ' . $this->protectIdentifiers($tableName) . ' DROP CONSTRAINT ' . $this->protectIdentifiers($indexName);
        }

        return 'DROP INDEX ' . $this->protectIdentifiers($indexName) . ' ON ' . $this->protectIdentifiers($tableName);
    }

    protected function makeDropTable(string $tableName, bool $ifExists = true): string {
        if (empty($tableName)) {
            throw new EDatabaseError('Table name can not be empty.');
        }

        $sql = 'DROP TABLE ';
        if ($ifExists == true) {
            $sql .= 'IF EXISTS ';
        }
        $sql .= $this->quoteIdentifier($tableName);

        return "$sql;\n";
    }

    protected function makeExistsTable(string $tableName): string {
        $sql = "SHOW TABLES LIKE '{$tableName}'";

        return "$sql;\n";
    }

    protected function makeIndexes(array $indexes, string $delimiter = ',') {
        $keys_count = 0;
        $sql        = '';

        foreach ($indexes as $index) {
            if (! $index instanceof IndexBuilderInterface) {
                throw new EDatabaseError(sprintf('Index must be an instance of `%s`, given `$s`', IndexBuilderInterface::class, gettype($index)));
            }

            $sql .= " " . (string) $index;

            // don't add a comma on the end of the last field
            if (++$keys_count < count($indexes)) {
                $sql .= $delimiter;
            }
        }

        return $sql;
    }

    protected function makeShowDatabases(): string {
        return 'SHOW DATABASES';
    }

    protected function makeShowIndexes(string $tableName, ?string $database = null): string {
        $sql = "SHOW INDEX FROM $tableName";
        if (is_string($database)) {
            $sql .= " FROM $database";
        }

        return $sql;
    }

    protected function makeShowTables(?string $database = null): string {
        $sql = 'SHOW TABLES';
        if (is_string($database)) {
            $sql .= " FROM $database";
        }

        return $sql;
    }

    protected function createColumnBuilder(string $type, mixed $constraint = null): ColumnBuilderInterface {
        return ColumnBuilder::create($this->getDatabase()->getPDODriverName(), [$type, $constraint, $this->getDatabase()]);
    }

    protected function createIndexBuilder(string $type, mixed $column, ?string $index_name = null): IndexBuilderInterface {
        return IndexBuilder::create($this->getDatabase()->getPDODriverName(), [$type, $column, $index_name, null, $this->getDatabase()]);
    }

    protected function splitSQLStatements(string $sql): array {
        $statements      = [];
        $buffer          = '';
        $inString        = false;
        $stringDelimiter = null;

        for ($i = 0, $len = strlen($sql); $i < $len; $i++) {
            $char = $sql[$i];

            // Проверяем, не открыта ли строка
            if ($inString) {
                if ($char === $stringDelimiter) {
                    // Проверяем, не экранирована ли кавычка
                    if ($i + 1 < $len && $sql[$i + 1] === $stringDelimiter) {
                        $buffer .= $char; // Двойная кавычка внутри строки
                        $i++;
                    } else {
                        $inString = false; // Закрываем строку
                    }
                }
            } elseif ($char === "'" || $char === '"') {
                $inString        = true;
                $stringDelimiter = $char;
            } elseif ($char === ';') {
                // Разделяем по `;`, если не внутри строки
                $statements[] = trim($buffer);
                $buffer       = '';
                continue;
            }

            $buffer .= $char;
        }

        if (! empty(trim($buffer))) {
            $statements[] = trim($buffer);
        }

        return $statements;
    }

    protected function execute($sql): mixed {
        $queries = $this->splitSQLStatements($sql);
        $result  = [];
        foreach ($queries as $query) {
            if (empty($query)) {
                continue;
            }

            $result[] = $this->realize($query, [], 'execute');
        }
        return $result;
    }

    protected function flush(): self {
        parent::flush();
        $this->keys    = [];
        $this->columns = [];

        return $this;
    }
}
/** End of SchemaBuilder **/