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

    public function createColumn(array | string | ColumnBuilderInterface $column, ?string $tableName = null): mixed {
        if (is_string($column)) {
            if (strpos($column, ' ') === false) {
                throw new EDatabaseError('Column information is required for that operation.');
            }
            $this->columns[] = $column;
        } elseif ($column instanceof ColumnBuilderInterface) {
            $this->columns[] = $column;
        } elseif (is_array($column)) {
            $this->columns = array_merge($this->columns, $column);
        }

        if (empty($tableName)) {
            return $this;
        }

        $sql = $this->makeAlterTableColumns('ADD', $tableName, $this->columns);

        return $this->execute($sql);
    }

    public function createDatabase(string $database, bool $ifNotExists = true, ?string $charSet = null, ?string $collate = null): mixed {
        $sql = $this->makeCreateDatabase($database, $ifNotExists, $charSet, $collate);

        return $this->execute($sql);
    }

    public function createIndex(array | string | IndexBuilderInterface $key, ?string $tableName = null): mixed {
        if (is_string($key) || $key instanceof IndexBuilderInterface) {
            $this->keys[] = $key;
        } elseif (is_array($key)) {
            $this->keys = array_merge($this->keys, $key);
        }

        if (empty($tableName)) {
            return $this;
        }

        $sql = $this->makeAlterTableKeys('ADD', $tableName, $this->keys);

        return $this->execute($sql);
    }

    public function createTable(string $tableName, bool $ifNotExists = true): mixed {
        if (empty($tableName)) {
            throw new EDatabaseError('A table name is required for that operation.');
        }

        if (count($this->columns) == 0) {
            throw new EDatabaseError('Field information is required.');
        }

        $sql = $this->makeCreateTable($tableName, $this->columns, $this->keys, $ifNotExists);

        return $this->execute($sql);
    }

    public function describeTable(string $tableName): mixed {
        $sql = $this->makeDescribeTable($tableName);

        return $this->realize($sql, [], 'fetchAll');
    }

    public function dropColumn(array | string $column, string $tableName): mixed {
        if (is_string($column)) {
            $this->columns[] = $column;
        } elseif (is_array($column)) {
            $this->columns = array_merge($this->columns, $column);
        }

        $sql = $this->makeAlterTableColumns('DROP', $tableName, $this->columns);

        return $this->execute($sql);
    }

    public function dropDatabase(string $database, bool $ifExists = true): mixed {
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

    public function dropTable(string $tableName, bool $ifExists = true): mixed {
        $sql = $this->makeDropTable($tableName, $ifExists);

        return $this->execute($sql);
    }

    public function existsTable(string $tableName): bool {
        $sql = $this->makeExistsTable($tableName);

        return $this->execute($sql) > 0;
    }

    public function index(array | string $column, ?string $indexName = null): IndexBuilderInterface {
        return $this->createIndexBuilder(IndexBuilder::TYPE_KEY, $column, $indexName);
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

        $sql = $this->makeAlterTableColumns('MODIFY', $tableName, $this->columns);

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

    protected function makeAlterTableColumns(string $alterSpec, string $tableName, array $columns = []): string {
        $alterSpec = strtoupper($alterSpec);
        $sql       = sprintf('ALTER TABLE %s %s;', $this->protectIdentifiers($tableName), $this->makeColumns($columns, $alterSpec));

        return $sql;
    }

    protected function makeAlterTableKeys(string $alterSpec, string $tableName, array $keys = []): string {
        $alterSpec = strtoupper($alterSpec);
        $sql       = sprintf('ALTER TABLE %s %s;', $this->protectIdentifiers($tableName), $this->makeKeys($keys, $alterSpec));

        return $sql;
    }

    protected function makeAlterTableName(string $fromTable, string $toTable): string {
        $sql = sprintf('ALTER TABLE %s RENAME TO %s;', $this->protectIdentifiers($fromTable), $this->protectIdentifiers($toTable));

        return $sql;
    }

    protected function makeColumn(array | string $column, array $attributes = [], ?string $alterSpec = null) {
        $sql = "\n\t";

        if ($alterSpec != null) {
            $sql .= "$alterSpec ";
        }

        $sql .= $this->protectIdentifiers($column);

        if ($alterSpec != null && strstr($alterSpec, 'DROP') !== false) {
            return $sql;
        }

        if (is_array($attributes) && ! empty($attributes)) {

            $attributes = array_change_key_case($attributes, CASE_UPPER);

            if (array_key_exists('NAME', $attributes)) {
                $sql .= ' ' . $this->protectIdentifiers($attributes['NAME']) . ' ';
            }

            if (array_key_exists('TYPE', $attributes)) {
                $sql .= ' ' . $attributes['TYPE'];

                if (array_key_exists('CONSTRAINT', $attributes)) {
                    switch ($attributes['TYPE']) {
                    case 'decimal':
                    case 'float':
                    case 'numeric':
                        $sql .= '(' . implode(',', $attributes['CONSTRAINT']) . ')';
                        break;

                    case 'enum':
                    case 'set':
                        $sql .= '("' . implode('","', $attributes['CONSTRAINT']) . '")';
                        break;

                    default:
                        $sql .= '(' . $attributes['CONSTRAINT'] . ')';
                    }
                }

                if (array_key_exists('UNSIGNED', $attributes) && $attributes['UNSIGNED'] === true) {
                    $sql .= ' UNSIGNED';
                }

                if (array_key_exists('DEFAULT', $attributes)) {
                    $sql .= ' DEFAULT \'' . $attributes['DEFAULT'] . '\'';
                }

                if (array_key_exists('NULL', $attributes) && $attributes['NULL'] === true) {
                    $sql .= ' NULL';
                } else {
                    $sql .= ' NOT NULL';
                }

                if (array_key_exists('AUTO_INCREMENT', $attributes) && $attributes['AUTO_INCREMENT'] === true) {
                    $sql .= ' AUTO_INCREMENT PRIMARY KEY';
                }
            }
        } elseif ($attributes instanceof ColumnBuilderInterface) {
            $sql .= ' ' . $this->protectIdentifiers($attributes);
        }

        return $sql;
    }

    protected function makeColumns(array $columns, ?string $alterSpec = null) {
        $columns_count = 0;
        $sql           = '';

        foreach ($columns as $column => $definition) {
            // Numeric field names aren't allowed in databases, so if the key is
            // numeric, we know it was assigned by PHP and the developer manually
            // entered the field information, so we'll simply add it to the list
            if (is_numeric($column)) {
                if($definition instanceof ColumnBuilderInterface){
                    $definition = (string)$definition;
                }

                $sql .= $this->makeColumn($definition, [], $alterSpec);
            } else {
                $sql .= $this->makeColumn($column, $definition, $alterSpec);
            }

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
        $sql = 'CREATE TABLE ';

        if ($ifNotExists === true) {
            $sql .= 'IF NOT EXISTS ';
        }

        $sql .= $this->quoteIdentifier($tableName) . " (";
        $sql .= $this->makeColumns($columns);
        $sql .= $this->makeKeys($keys);
        $sql .= ");";

        return $sql;
    }

    protected function makeDescribeTable(string $tableName): string {
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

    protected function makeKey(array|IndexBuilderInterface $key, ?string $alterSpec = null) {
        $sql = "\n\t";

        if ($alterSpec != null) {
            $sql .= "$alterSpec ";
        }

        // if key is array
        if (is_array($key)) {
            $attributes = array_change_key_case($key, CASE_UPPER);

            $column = [];
            if (array_key_exists('COLUMN', $attributes)) {
                if (is_array($attributes['COLUMN'])) {
                    $column = $attributes['COLUMN'];
                } elseif (is_string($attributes['COLUMN'])) {
                    $column[] = $attributes['COLUMN'];
                }
            }

            $indexName = implode('_', $column);
            if (array_key_exists('INDEXNAME', $attributes)) {
                $indexName = $attributes['INDEXNAME'];
            }

            $type      = 'INDEX';
            $isPrimary = false;
            if (array_key_exists('TYPE', $attributes)) {
                if (strpos('PRIMARY', strtoupper($attributes['TYPE'])) === true || strtoupper($attributes['TYPE']) == 'PRIMARY') {
                    $type      = 'PRIMARY KEY';
                    $isPrimary = true;
                } else {
                    $type = strtoupper($attributes['TYPE']);
                }
            }

            $indexName = $this->protectIdentifiers($indexName);
            $column    = $this->protectIdentifiers($column);

            $sql .= ' ' . $type . ($isPrimary != true ? ' ' . $indexName : '') . ' (' . implode(',', $column) . ')';
        }
        elseif ($key instanceof IndexBuilderInterface) {
            $sql .= " ".(string)$key;
        }

        return $sql;
    }

    protected function makeKeys(array $keys, ?string $alterSpec = null) {
        $keys_count = 0;
        $sql        = '';

        foreach ($keys as $key => $attributes) {
            if (is_numeric($key)) {
                $sql .= $this->makeKey($attributes, $alterSpec);
            } else {
                $sql .= $this->makeKey($key, $alterSpec);
            }

            // don't add a comma on the end of the last field
            if (++$keys_count < count($keys)) {
                $sql .= ',';
            }
        }

        return ($alterSpec == null && $keys_count > 0 ? ",\n\t" : "") . $sql;
    }

    protected function makeShowDatabases(): string {
        return 'SHOW DATABASES';
    }

    protected function makeShowIndexes(string $table_name, ?string $database = null): string {
        $sql = "SHOW INDEX FROM $table_name";
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
        return IndexBuilder::create($this->getDatabase()->getPDODriverName(), [$type, $column, $index_name, $this->getDatabase()]);
    }

    protected function execute($sql): mixed {
        return $this->realize($sql, [], 'execute');
    }

    protected function flush(): self {
        parent::flush();
        $this->keys    = [];
        $this->columns = [];

        return $this;
    }
}
/** End of SchemaBuilder **/