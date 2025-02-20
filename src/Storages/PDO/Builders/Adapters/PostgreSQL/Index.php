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

use Scaleum\Stdlib\Exceptions\EDatabaseError;
use Scaleum\Storages\PDO\Builders\IndexBuilder;


/**
 * Index
 *
 * @author Maxim Kirichenko <kirichenko.maxim@gmail.com>
 */
class Index extends IndexBuilder
{
    protected string $identifierQuoteLeft  = '"';
    protected string $identifierQuoteRight = '"';
    protected function makeFulltext(): string {
        throw new EDatabaseError('PostgreSQL does not support fulltext indexes');
    }

    protected function makeIndex(): string{
        if ($this->table === null){
            throw new EDatabaseError('Table name is not defined');
        }
        return parent::makeIndex();
    }
}
/** End of Index **/