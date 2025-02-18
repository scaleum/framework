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

namespace Scaleum\Storages\PDO\Builders\Adapters\Oracle;

use Scaleum\Storages\PDO\Builders\IndexBuilder;


/**
 * Index
 *
 * @author Maxim Kirichenko <kirichenko.maxim@gmail.com>
 */
class Index extends IndexBuilder
{
    protected function makeFulltext(): string {
        $indexName = $this->protectIdentifiers($this->indexName);
        $column    = implode(',', $this->protectIdentifiers($this->columns));
        return "INDEX $indexName ON $column INDEXTYPE IS CTXSYS.CONTEXT";
    }
}
/** End of Index **/