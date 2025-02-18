<?php
declare(strict_types=1);
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
 * SchemaBuilderInterface
 *
 * @author Maxim Kirichenko <kirichenko.maxim@gmail.com>
 */
interface SchemaBuilderInterface
{
    public function addColumn($column): mixed;
    public function addIndex($key): mixed;
    public function columnBigInt(?int $length = null): ColumnBuilderInterface;
    public function columnBigPk(?int $length = null): ColumnBuilderInterface;
    public function columnBinary(?int $length = null): ColumnBuilderInterface;
    public function columnBoolean(): ColumnBuilderInterface;
    public function columnDate(): ColumnBuilderInterface;
    public function columnDateTime(): ColumnBuilderInterface;
    public function columnDecimal(mixed $precision = null): ColumnBuilderInterface;
    public function columnDouble(mixed $precision = null): ColumnBuilderInterface;
    public function columnFloat(mixed $precision = null): ColumnBuilderInterface;
    public function columnInt(?int $length = null): ColumnBuilderInterface;
    public function columnJson(): ColumnBuilderInterface;
    public function columnLongText(): ColumnBuilderInterface;
    public function columnMediumText(): ColumnBuilderInterface;
    public function columnMoney(mixed $precision = null): ColumnBuilderInterface;
    public function columnPrimaryKey(?int $length = null): ColumnBuilderInterface;
    public function columnSmallint(?int $length = null): ColumnBuilderInterface;
    public function columnString(?int $length = null): ColumnBuilderInterface;
    public function columnText(): ColumnBuilderInterface;
    public function columnTime(mixed $precision = null): ColumnBuilderInterface;
    public function columnTimestamp(mixed $precision = null): ColumnBuilderInterface;
    public function columnTinyint(?int $length = null): ColumnBuilderInterface;
    public function createColumn(array|string|ColumnBuilderInterface $column, ?string $tableName = null): mixed;
    public function createDatabase(string $database, bool $ifNotExists = true, ?string $charSet = null, ?string $collate = null): mixed;
    public function createIndex(array|string|IndexBuilderInterface $key, ?string $tableName = null): mixed;
    public function createTable(string $tableName, bool $ifNotExists = true): mixed;
    public function describeTable(string $tableName): mixed;
    public function dropColumn(array|string $column, string $tableName): mixed;
    public function dropDatabase(string $database, bool $ifExists = true): mixed;
    public function dropIndex(string $indexName, string $tableName): mixed;
    public function dropPrimaryKey(string $indexName, string $tableName): mixed;
    public function dropTable(string $tableName, bool $ifExists = true): mixed;
    public function existsTable(string $tableName): bool;
    public function index(array|string $column, ?string $indexName = null): IndexBuilderInterface;
    public function indexFulltext(array|string $column, ?string $indexName = null): IndexBuilderInterface;
    public function indexUnique(array|string $column, ?string $indexName = null): IndexBuilderInterface;
    public function indexForeign(array|string $column, ?string $indexName = null): IndexBuilderInterface;
    public function modifyColumn(mixed $column, string $tableName): mixed;
    public function primaryKey(mixed $column): IndexBuilderInterface;
    public function renameTable(string $fromTable, string $toTable): mixed;
    public function showDatabases(): mixed;
    public function showIndex(string $tableName, ?string $database = null): mixed;
    public function showTables(?string $database = null): mixed;
    public function prepare(bool $value = false): self;
    public function optimize(bool $value = false): self;    
}
/** End of SchemaBuilderInterface **/