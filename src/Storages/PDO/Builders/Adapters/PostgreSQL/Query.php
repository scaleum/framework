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

namespace Scaleum\Storages\PDO\Builders\Adapters\PostgreSQL;

use Scaleum\Storages\PDO\Builders\QueryBuilder;


/**
 * Query 
 *
 * @author Maxim Kirichenko <kirichenko.maxim@gmail.com>
 */
class Query extends QueryBuilder
{
    protected string $identifierQuoteLeft  = '"';
    protected string $identifierQuoteRight = '"';
    
    protected function makeLimit(string $sql, int $limit, int $offset): string {
        $normalizedSql = strtoupper(preg_replace('/\s+/', ' ', trim($sql)));
        if (preg_match('/^(UPDATE|DELETE|INSERT)\s/i', $normalizedSql, $matches)){
            return $sql;
        }
                
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
}
/** End of Query **/