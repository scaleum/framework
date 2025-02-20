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

use Scaleum\Storages\PDO\Builders\QueryBuilder;

/**
 * Query
 *
 * @author Maxim Kirichenko <kirichenko.maxim@gmail.com>
 */
class Query extends QueryBuilder {
    protected function makeLimit(string $sql, int $limit, int $offset): string {
        $normalizedSql = strtoupper(preg_replace('/\s+/', ' ', trim($sql)));
        if (preg_match('/^(UPDATE|DELETE|INSERT)\s/i', $normalizedSql, $matches)){
            return $sql;
        }

        if (! str_contains($normalizedSql, "ORDER BY")) {
            $sql .= " ORDER BY (SELECT NULL)";
        }
        $sql .= " OFFSET " . ($offset ?? 0) . " ROWS";
        
        if ($limit > 0) {
            $sql .= " FETCH NEXT $limit ROWS ONLY";
        }

        return $sql;
    }
}
/** End of Query **/