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

namespace Scaleum\Storages\PDO\Builders\Contracts;

/**
 * IndexBuilderInterface
 *
 * @author Maxim Kirichenko <kirichenko.maxim@gmail.com>
 */
interface IndexBuilderInterface {
    public function name(string $index_name): self;
    public function column(array | string $column): self;
    public function reference(string $tableName, array | string $column): self;
}
/** End of IndexBuilderInterface **/