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

/**
 * ModelAbstract
 *
 * @author Maxim Kirichenko <kirichenko.maxim@gmail.com>
 */
abstract class ModelAbstract extends DatabaseProvider {
    public const ON_INSERT   = 0x01;
    public const ON_UPDATE   = 0x02;
    public const ON_DELETE   = 0x04;
    public const ON_TRUNCATE = 0x08;

    protected ?string $table     = null;
    protected string $primaryKey = 'id';
    protected ModelData $data;
    protected ?string $mode     = null;
    protected array $lastStatus = [];

    public function __construct(?Database $database = null) {
        parent::__construct($database);
        $this->data = new ModelData();
    }

    public function __get(string $name): mixed {
        return $this->data->$name ?? null;
    }

    public function __set(string $name, mixed $value): void {
        $this->data->$name = $value;
    }

    public function find(mixed $id): ?self {
        $db = $this->getDatabase();
        if (! $db) {
            return null;
        }

        $data = $db->setQuery("SELECT * FROM {$this->getTable()} WHERE {$this->primaryKey} = :id LIMIT 1", ['id' => $id])->fetch();
        if (! $data) {
            return null;
        }

        $this->data = new ModelData($data);
        $this->loadRelations($this->data);
        return $this;
    }

    public function findOneBy(array $conditions): ?self {
        $db = $this->getDatabase();
        if (! $db) {
            return null;
        }

        $whereClauses = implode(" AND ", array_map(fn($key) => "$key = :$key", array_keys($conditions)));
        $sql          = "SELECT * FROM {$this->table} WHERE {$whereClauses} LIMIT 1";
        $data         = $db->setQuery($sql, $conditions)->fetch();
        if (! $data) {
            return null;
        }

        $this->data = new ModelData($data);
        $this->loadRelations($this->data);
        return $this;
    }

    public function findAll(): array {
        $db = $this->getDatabase();
        if (! $db) {
            return [];
        }

        $data    = $db->setQuery("SELECT * FROM {$this->getTable()}")->fetchAll();
        $results = [];
        foreach ($data as $row) {
            $model       = new static($db);
            $model->data = new ModelData($row);
            $model->loadRelations($model->data);
            $results[] = $model;
        }

        return $results;
    }

    public function findAllBy(array $conditions): array {
        $db = $this->getDatabase();
        if (! $db) {
            return [];
        }

        $whereClauses = implode(" AND ", array_map(fn($key) => "$key = :$key", array_keys($conditions)));
        $sql          = "SELECT * FROM {$this->table} WHERE {$whereClauses}";
        $data         = $db->setQuery($sql, $conditions)->fetchAll();
        $results      = [];
        foreach ($data as $row) {
            $model       = new static($db);
            $model->data = new ModelData($row);
            $model->loadRelations($model->data);

            $results[] = $model;
        }

        return $results;
    }

    public function load(array $input): self {
        $relations = $this->getRelations();
        //TODO: Добавить снятие слепка "до" и "после" загрузки, чтобы можно было фиксировать изменения
        if ($this->beforeLoad()) {
            foreach ($input as $key => $value) {
                if (array_key_exists($key, $relations)) {
                    $relationDefinition = $relations[$key];
                    $relationModel      = $relationDefinition['model'];
                    $relationType       = $relationDefinition['type'];

                    if ($relationType === 'hasMany' && is_array($value)) {
                        $this->$key = array_map(fn($item) => (new $relationModel($this->getDatabase()))->load($item), $value);
                    } elseif (($relationType === 'hasOne' || $relationType === 'belongsTo') && is_array($value)) {
                        if ($this->$key instanceof ModelAbstract) {
                            $primaryKey = $this->$key->primaryKey;
                            if (isset($value[$primaryKey]) && $value[$primaryKey] === $this->$key->$primaryKey) {
                                $this->$key->load($value);
                            } else {
                                $this->$key = (new $relationModel($this->getDatabase()))->load($value);
                            }
                        } else {
                            $this->$key = (new $relationModel($this->getDatabase()))->load($value);
                        }
                    }
                } else {
                    $this->$key = $value;
                }
            }
            $this->afterLoad();
        }

        return $this;
    }

    public function insert(): int {
        $result = 0;
        if ($this->isTransactional(self::ON_INSERT)) {
            $db = $this->getDatabase();
            $db->begin();
            try {
                $result = $this->insertInternal();
                if ($result) {
                    $db->commit();
                } else {
                    $db->rollBack();
                }
            } catch (\Exception $e) {
                $db->rollBack();
                throw $e;
            }
        } else {
            $result = $this->insertInternal();
        }

        return $result;
    }

    protected function insertInternal(): int {
        $this->lastStatus = [];
        $result           = 0;

        $db = $this->getDatabase();
        if (! $db || empty($this->data->toArray())) {
            $this->lastStatus = [
                'status'      => (bool) $result,
                'status_text' => 'Database connection error or empty data',
            ];
            return $result;
        }

        if ($this->beforeInsert()) {
            $data         = $this->clearRelations($this->data->toArray());
            $columns      = implode(", ", array_keys($data));
            $placeholders = ":" . implode(", :", array_keys($data));

            $result       = $db->setQuery("INSERT INTO {$this->getTable()} ({$columns}) VALUES ({$placeholders})", $data)->execute();
            $lastInsertId = $db->getLastInsertID();

            if ($lastInsertId) {
                $this->data->{$this->primaryKey} = $lastInsertId;
            }

            $relationResults  = $this->syncRelations();
            $this->lastStatus = [
                'status'      => (bool) $result,
                'status_text' => $lastInsertId ? 'Record inserted' : 'Record not inserted',
                'relations'   => $relationResults,
            ];
            $this->afterInsert();
        }

        return $result;
    }

    public function update(): int {
        if (! $this->isExisting()) {
            return $this->insert();
        }

        $result = 0;
        if ($this->isTransactional(self::ON_UPDATE)) {
            $db = $this->getDatabase();
            $db->begin();
            try {
                $result = $this->updateInternal();
                if ($result) {
                    $db->commit();
                } else {
                    $db->rollBack();
                }
            } catch (\Exception $e) {
                $db->rollBack();
                throw $e;
            }
        } else {
            $result = $this->updateInternal();
        }
        return $result;
    }

    protected function updateInternal(): int {
        $this->lastStatus = [];
        $result           = 0;

        $db = $this->getDatabase();
        if (! $db || empty($this->data->toArray())) {
            $this->lastStatus = [
                'status'      => (bool) $result,
                'status_text' => 'Database connection error or empty data',
            ];
            return $result;
        }

        if ($this->beforeUpdate()) {
            $data             = $this->clearRelations($this->data->toArray());
            $fields           = implode(", ", array_map(fn($key) => "$key = :$key", array_keys($data)));
            $status           = $db->setQuery("UPDATE {$this->getTable()} SET {$fields} WHERE {$this->primaryKey} = :{$this->primaryKey}", $data)->execute();
            $relationResults  = $this->syncRelations();
            $this->lastStatus = [
                'status'      => (bool) $status,
                'status_text' => $status ? 'Record updated' : 'Record not updated',
                'relations'   => $relationResults,
            ];

            $this->afterUpdate();
        }

        return $result;
    }

    public function delete(bool $cascade = false): int {
        $result = 0;
        if (! $this->isExisting()) {
            $this->lastStatus = [
                'status'      => (bool) $result,
                'status_text' => 'Record is not loaded or does not exist',
            ];
            return $result;
        }

        if ($this->isTransactional(self::ON_DELETE)) {
            $db = $this->getDatabase();
            $db->begin();
            try {
                $result = $this->deleteInternal($cascade);
                if ($result) {
                    $db->commit();
                } else {
                    $db->rollBack();
                }
            } catch (\Exception $e) {
                $db->rollBack();
                throw $e;
            }
        } else {
            $result = $this->deleteInternal($cascade);
        }

        return $result;
    }

    protected function deleteInternal(bool $cascade = false): int {
        $this->lastStatus = [];
        $result           = 0;

        $db = $this->getDatabase();
        if (! $db || ! $this->data->has($this->primaryKey)) {
            $this->lastStatus = [
                'status'      => (bool) $result,
                'status_text' => 'Database connection error or empty data',
            ];
            return $result;
        }

        if ($this->beforeDelete()) {
            $id              = $this->data->{$this->primaryKey};
            $relationResults = $cascade ? $this->removeRelations() : [];
            $result          = $db->setQuery("DELETE FROM {$this->getTable()} WHERE {$this->primaryKey} = :id", ['id' => $id])->execute();

            if ($result) {
                $this->data = new ModelData(); // Очищаем данные после удаления
            }

            $this->lastStatus = [
                'status'      => (bool) $result,
                'status_text' => $result ? 'Record deleted' : 'Record not deleted',
                'relations'   => $relationResults,
            ];

            $this->afterDelete();
        }

        return $result;
    }

    public function isExisting(): bool {
        return $this->data->has($this->primaryKey);
    }

    public function getId(): mixed {
        return $this->data->{$this->primaryKey};
    }

    public function getMode(): ?string {
        return $this->mode;
    }

    public function setMode(string $mode): self {
        $this->mode = $mode;

        return $this;
    }

    /**
     * Declares which DB operations should be performed within a transaction in different scenarios.
     * The supported DB operations are: [[ON_INSERT]], [[ON_UPDATE]], [[ON_DELETE]] and [[ON_TRUNCATE]]
     * which correspond to the [[insert()]], [[update()]] ,[[delete()]] and [[truncate]] methods, respectively.
     * By default, these methods are NOT enclosed in a DB transaction.
     *
     * In some modes, to ensure data consistency, you may want to enclose some or all of them
     * in transactions. You can do so by overriding this method and returning the operations
     * that need to be transactional. For example,
     *
     * return [
     *     'admin' => self::ON_INSERT,
     *     'user' => self::ON_INSERT | self::ON_UPDATE | self::ON_DELETE | self::ON_TRUNCATE,
     * ];
     *
     * The above declsyncRelationsfies that in the "admin" mode, the insert operation ([[insert()]])
     * should be done in a transaction; and in the "user" mode, all the operations should be done
     * in a transaction.
     *
     * @return array the declarations of transactional operations. The array keys are modes names,
     * and the array values are the corresponding transaction operations.
     */
    public function getTransactions() {return [];}

    public function isTransactional(int $type): bool {
        $mode         = $this->getMode();
        $transactions = $this->getTransactions();

        return isset($transactions[$mode]) && ($transactions[$mode] & $type);
    }

    public function getTable(): string {
        if (! $this->table) {
            $this->table = strtolower((new \ReflectionClass($this))->getShortName());
        }

        return $this->table;
    }

    public function getPrimaryKey(): string {
        return $this->primaryKey;
    }

    /**
     * Get the value of data
     */
    public function getData() {
        return $this->data;
    }

    /**
     * Get the value of lastStatus
     */
    public function getLastStatus(): array {
        return $this->lastStatus;
    }

    protected function getRelations(): array {
        return [];
    }

    public function toArray():array{
        return $this->clearRelations($this->data->toArray());
    }

    private function loadRelations(ModelData $modelData): void {
        foreach ($this->getRelations() as $relation => $config) {
            $relatedModel  = new $config['model']($this->getDatabase());
            $relatedMethod = $config['method'];
            $primaryKey    = $config['primary_key'];

            $this->$relation = $relatedModel->$relatedMethod($modelData->{$primaryKey});
        }
    }

    private function syncRelations(): array {
        $result = [];
        foreach ($this->getRelations() as $relation => $config) {
            /** @var ModelAbstract $relatedData */
            $relatedData = $this->$relation ?? null;
            if ($relatedData === null) {
                continue;
            }

            $foreignKey = $config['foreign_key'];

            if ($config['type'] === 'hasOne' || $config['type'] === 'belongsTo') {
                $relatedData->$foreignKey = $this->{$this->primaryKey};
                if ($relatedData->{$relatedData->primaryKey}) {
                    $relatedData->updateInternal();
                    $result[$relation] = 'updated';
                } else {
                    $relatedData->insertInternal();
                    $result[$relation] = 'inserted';
                }
            } elseif ($config['type'] === 'hasMany') {
                $updated  = 0;
                $inserted = 0;
                foreach ($relatedData as $item) {
                    $item->$foreignKey = $this->{$this->primaryKey};
                    if ($item->{$item->primaryKey}) {
                        $updated += $item->update() ? 1 : 0;
                    } else {
                        $inserted += $item->insert() ? 1 : 0;
                    }
                }
                $result[$relation] = [
                    'updated'  => $updated,
                    'inserted' => $inserted,
                ];
            }
        }
        return $result;
    }

    private function removeRelations(): array {
        $result = [];
        foreach ($this->getRelations() as $relation => $config) {
            $relatedData = $this->$relation ?? null;
            if ($relatedData === null) {
                continue;
            }

            if ($config['type'] === 'hasOne' || $config['type'] === 'belongsTo') {
                $relatedData->delete();
                $result[$relation] = 'deleted';
            } elseif ($config['type'] === 'hasMany' && is_array($relatedData)) {
                $deletedCount = 0;
                foreach ($relatedData as $item) {
                    $item->delete();
                    $deletedCount++;
                }
                $result[$relation] = ['deleted' => $deletedCount];
            }
        }
        return $result;
    }

    private function clearRelations(array $data): array {
        foreach ($this->getRelations() as $relation => $definition) {
            if (isset($data[$relation])) {
                unset($data[$relation]);
            }
        }

        return $data;
    }

    /**
     * What should I do after deleting?
     * @return mixed
     */
    protected function afterDelete() {

    }

    /**
     * What should I do after inserting?
     * @return mixed
     */
    protected function afterInsert() {

    }

    /**
     * What should I do after loading?
     * @return mixed
     */
    protected function afterLoad() {

    }

    /**
     * What should I do after updating?
     * @return mixed
     */
    protected function afterUpdate() {

    }

    /**
     * What should I do before deleting? You must always return a Boolean value
     * @return bool [FALSE|TRUE]
     */
    protected function beforeDelete() {
        return true;
    }

    /**
     * What should I do before inserting? You must always return a Boolean value
     * @return bool [FALSE|TRUE]
     */
    protected function beforeInsert() {
        return true;
    }

    /**
     * What should I do before load? You must always return a Boolean value
     * @return bool [FALSE|TRUE]
     */
    protected function beforeLoad() {
        return true;
    }

    /**
     * What should I do before updating? You must always return a Boolean value
     * @return bool [FALSE|TRUE]
     */
    protected function beforeUpdate() {
        return true;
    }
}
/** End of ModelAbstract **/