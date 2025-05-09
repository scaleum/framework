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

use Scaleum\Stdlib\Exceptions\ERuntimeError;
use Scaleum\Storages\PDO\Database;

/**
 * DatabaseSession
 *
 * @author Maxim Kirichenko <kirichenko.maxim@gmail.com>
 */
class DatabaseSession extends SessionAbstract {

    protected ?Database $database  = null;
    protected string $table        = 'sessions';
    protected bool $autoDeployment = false;

    public function open($name): bool {
        if ($this->autoDeployment) {
            // Create table if not exists
            $schema = $this->getDatabase()->getSchemaBuilder();
            $schema
                ->addColumn([
                    $schema->columnString(64)->setColumn('session_id')->setNotNull(TRUE),
                    $schema->columnText()->setColumn('data'),
                    $schema->columnInt(11)->setColumn('last_activity')->setDefaultValue(0),
                ])
                ->addIndex([
                    $schema->primaryKey('session_id'),
                ])
                ->createTable($this->table, true);
        }

        return parent::open($name);
    }

    protected function read(): array {
        $result  = [];
        $builder = $this->getDatabase()->getQueryBuilder();
        if ($row = $builder->select()->from($this->table)->where('session_id', $this->id)->limit(1)->row()) {
            $result                  = unserialize(gzuncompress(base64_decode($row['data'])));
            $result['last_activity'] = $row['last_activity'] ?? 0;
        }
        return $result;
    }

    protected function write(array $data): void {
        $time = $data['last_activity'] ?? $this->getTimestamp();
        unset($data['last_activity']);

        $dataPrepared = base64_encode(gzcompress(serialize($data)));
        $query        = $this->getDatabase()->getQueryBuilder();
        $count        = $query->select('COUNT(*)')->from($this->table)->where('session_id', $this->id)->rowColumn();
        if ($count > 0) {
            $query->set(['data' => $dataPrepared, 'last_activity' => $time])->where('session_id', $this->id)->update($this->table);
        } else {
            $query->set(['data' => $dataPrepared, 'last_activity' => $time, 'session_id' => $this->id])->insert($this->table);
        }
    }

    protected function delete(): void {
        $query = $this->getDatabase()->getQueryBuilder();
        $query->where('session_id', $this->id)->delete($this->table);
    }

    public function cleanup(): void {
        if ((rand() % 100) < 5) {
            $query = $this->getDatabase()->getQueryBuilder();
            $query->where("last_activity < ", $this->getTimestamp() - $this->getExpiration(), false)->delete($this->table);
        }
    }
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

    /**
     * Get the value of autoDeployment
     */
    public function getAutoDeployment(): bool {
        return $this->autoDeployment;
    }

    /**
     * Set the value of autoDeployment
     *
     * @return  self
     */
    public function setAutoDeployment(bool $value) {
        $this->autoDeployment = $value;
        return $this;
    }
}
/** End of DatabaseSession **/