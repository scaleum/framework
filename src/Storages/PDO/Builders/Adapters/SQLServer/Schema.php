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

namespace Scaleum\Storages\PDO\Builders\Adapters\SQLServer;

use Scaleum\Stdlib\Exceptions\EDatabaseError;
use Scaleum\Storages\PDO\Builders\SchemaBuilder;

/**
 * Schema
 *
 * @author Maxim Kirichenko <kirichenko.maxim@gmail.com>
 */
class Schema extends SchemaBuilder {
    protected string $identifierQuoteLeft  = "[";
    protected string $identifierQuoteRight = "]";

    protected function makeDropTable(string $tableName, bool $ifExists = false): string {
        if (empty($tableName)) {
            throw new EDatabaseError('Table name can not be empty.');
        }

        $tableName = $this->protectIdentifiers($tableName);
        $sql       = ($ifExists ? "IF OBJECT_ID('[$tableName]', 'U') IS NOT NULL " : "") . "DROP TABLE $tableName;";

        return $sql;
    }

    protected function makeAlterTableName(string $fromTable, string $toTable): string {
        return sprintf('sp_rename %s, %s;', $this->protectIdentifiers($fromTable), $this->protectIdentifiers($toTable));
    }    

    protected function makeShowTables(?string $database = null): string {
        return "SELECT TABLE_NAME FROM sysibm.tables";
    }

    protected function makeShowDatabases(): string {
        return 'EXEC SP_HELPDB';
    }    
}
/** End of Schema **/