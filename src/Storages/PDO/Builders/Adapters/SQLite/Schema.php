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

namespace Scaleum\Storages\PDO\Builders\Adapters\SQLite;

use Scaleum\Stdlib\Exceptions\EDatabaseError;
use Scaleum\Storages\PDO\Builders\SchemaBuilder;

/**
 * Schema
 *
 * @author Maxim Kirichenko <kirichenko.maxim@gmail.com>
 */
class Schema extends SchemaBuilder {
    protected string $identifierQuoteLeft  = '"';
    protected string $identifierQuoteRight = '"';

    protected function makeShowIndexes(string $table_name, ?string $database = null): string {
        return "PRAGMA index_list({$this->protectIdentifiers($table_name)});";
    }

    protected function makeShowTable(string $tableName): string {
        return "PRAGMA table_info({$this->protectIdentifiers($tableName)});";
    }

    protected function makeShowTables(?string $database = null): string {
        return "SELECT name FROM sqlite_schema WHERE type = 'table' AND name NOT LIKE 'sqlite_%'";
    }

    protected function makeShowDatabases(): string {
        // SQLite does not support multiple databases
        throw new EDatabaseError('SQLite does not support multiple databases');
    }

    protected function makeDropIndex(string $indexName, string $tableName, bool $isPrimaryKey = false): string {
        if ($isPrimaryKey == true) {
            return 'ALTER TABLE ' . $this->protectIdentifiers($tableName) . ' DROP CONSTRAINT ' . $this->protectIdentifiers($indexName);
        }

        return 'DROP INDEX IF EXISTS ' . $this->protectIdentifiers($indexName);
    }

    protected function makeAlterTableColumns(string $alterSpec, string $tableName, array $columns = []): string {
        // SQLite does support one definition per ALTER TABLE statement
        // So we will loop through each column and add it to the query
        $result = '';
        foreach ($columns as $column) {
            $result .= parent::makeAlterTableColumns($alterSpec, $tableName, [$column]);
        }
        return $result;
    }
}
/** End of Schema **/