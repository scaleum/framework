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

namespace Scaleum\Storages\PDO;


/**
 * DatabaseProviderInterface
 *
 * @author Maxim Kirichenko <kirichenko.maxim@gmail.com>
 */
interface DatabaseProviderInterface
{
    public function getDatabase(): Database;
    public function setDatabase(Database $db): void;
}
/** End of DatabaseProviderInterface **/