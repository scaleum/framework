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

namespace Scaleum\Storages\PDO\Builders\Adapters\PostgreSQL;

use Scaleum\Storages\PDO\Builders\Contracts\ColumnBuilderInterface;
use Scaleum\Storages\PDO\Builders\SchemaBuilder;

/**
 * Schema
 *
 * @author Maxim Kirichenko <kirichenko.maxim@gmail.com>
 */
class Schema extends SchemaBuilder {
    protected string $identifierQuoteLeft  = '"';
    protected string $identifierQuoteRight = '"';

    protected function makeDropIndex(string $indexName, string $tableName, bool $isPrimaryKey = false): string {
        if ($isPrimaryKey == true) {
            return 'ALTER TABLE ' . $this->protectIdentifiers($tableName) . ' DROP CONSTRAINT ' . $this->protectIdentifiers($indexName);
        }
        return 'DROP INDEX IF EXISTS ' . $this->protectIdentifiers($indexName);
    }

    protected function makeShowTables(?string $database = null): string{
        return "SELECT * FROM pg_catalog.pg_tables WHERE schemaname != 'pg_catalog' AND schemaname != 'information_schema';";
    }    
    protected function makeShowTable(string $tableName): string {
        return "SELECT * FROM information_schema.columns WHERE table_name = '$tableName';";
    }

    protected function makeShowIndexes(string $table_name, ?string $database = null): string {
        return "SELECT * FROM pg_indexes WHERE tablename = '$table_name';";
    }

    protected function makeShowDatabases(): string {
        return "SELECT * FROM pg_database WHERE datistemplate = false;";
    }
}
/** End of Schema **/