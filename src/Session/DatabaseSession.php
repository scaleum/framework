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

namespace Scaleum\Session;

use PDO;
use Scaleum\Stdlib\Exceptions\ERuntimeError;
use Scaleum\Storages\PDO\Database;

/**
 * DatabaseSession
 *
 * @author Maxim Kirichenko <kirichenko.maxim@gmail.com>
 */
class DatabaseSession extends SessionAbstract {

    protected ?Database $database = null;
    protected string $table       = 'sessions';

    public function __construct(array $config = []) {
        parent::__construct($config);

        // Create table if not exists
        $schema = $this->getDatabase()->getSchemaBuilder();
        $schema
            ->addColumn([
                $schema->columnString(64)->setColumn('session_id')->setNotNull(TRUE),
                $schema->columnText()->setColumn('data'),
                $schema->columnInt(11)->setColumn('updated_at')->setDefaultValue(0),
            ])
            ->addIndex([
                $schema->primaryKey('session_id'),
            ])
            ->createTable($this->table, true);

        // $sql = sprintf(
        //     'CREATE TABLE IF NOT EXISTS `%s` (
        //         `session_id` VARCHAR(64) NOT NULL PRIMARY KEY,
        //         `data` TEXT NOT NULL,
        //         `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        //     ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;',
        //     str_replace('`', '``', $this->table) // защита от инъекций
        // );
    }

    protected function read(): array {
        $result = [];
        $query  = $this->getDatabase()->getQueryBuilder();
        if ($row = $query->prepare(false)->optimize(false)->select()->from($this->table)->where('session_id', $this->id)->limit(1)->row()) {
            $result = json_decode($row['data'], TRUE);
        }
        return $result;
    }

    protected function write(array $data): void {
        $count = $this->getDatabase()->setQuery('SELECT COUNT(*) FROM sessions WHERE session_id = :id', ['id' => $this->id])->fetchColumn();
        $sql   = ($count > 0) ? 'UPDATE sessions SET data = :data, updated_at = :updated_at WHERE session_id = :id' : 'INSERT INTO sessions (session_id, data, updated_at) VALUES (:id, :data, :updated_at)';

        $this->getDatabase()->setQuery($sql, ['id' => $this->id, 'data' => json_encode($data, JSON_FORCE_OBJECT | JSON_UNESCAPED_UNICODE), 'updated_at' => time()])->execute();
    }

    protected function delete(): void {}
    public function cleanup(): void {}
    public function close() {
        parent::close();
        $this->cleanup();
    }

    public function getDatabase() {
        if (! $this->database instanceof Database) {
            throw new ERuntimeError(
                sprintf(
                    "Database service not defined or is not an instance of `%a`, given `%s`.",
                    Database::class,
                    is_object($this->database) ? get_class($this->database) : gettype($this->database)
                )
            );
        }
        return $this->database;
    }

    public function setDatabase(Database $database) {
        $this->database = $database;
        return $this;
    }
}
/** End of DatabaseSession **/