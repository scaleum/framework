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

namespace Scaleum\Storages\PDO;

use Scaleum\Services\ServiceLocator;
use Scaleum\Stdlib\Exceptions\ERuntimeError;

/**
 * DatabaseProvider
 *
 * @author Maxim Kirichenko <kirichenko.maxim@gmail.com>
 */
class DatabaseProvider implements DatabaseProviderInterface {
    protected ?Database $database  = null;
    protected string $serviceName = 'db';

    public function __construct(?Database $database = null) {
        if ($database !== null) {
            $this->setDatabase($database);
        }
    }

    public function getDatabase(): Database {
        if ($this->database === null) {
            // get database service
            if (! ($db = ServiceLocator::get($this->serviceName, null)) instanceof Database) {
                throw new ERuntimeError(
                    sprintf(
                        "Database service `%s` not found or is not an instance of `%a`, given `%s`.",
                        $this->serviceName,
                        Database::class,
                        is_object($db) ? get_class($db) : gettype($db)
                    )
                );
            }
            $this->database = $db;
        }
        return $this->database;
    }

    public function setDatabase(Database $database): void {
        $this->database = $database;
    }
}
/** End of DatabaseProvider **/