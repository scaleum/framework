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

use Scaleum\Stdlib\Exceptions\EDatabaseError;
use Scaleum\Stdlib\Exceptions\EInvalidArgumentException;

/**
 * ModelAbstract
 *
 * @author Maxim Kirichenko <kirichenko.maxim@gmail.com>
 */
abstract class ModelAbstract extends DatabaseProvider implements ModelInterface{
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

    /**
     * Finds a record by its ID.
     *
     * @param mixed $id The ID of the record to find.
     * @return self|null The found record as an instance of the class, or null if not found.
     */
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

    /**
     * Finds a single record based on the specified conditions.
     *
     * @param array $conditions An associative array of conditions to match against.
     * @return self|null The found record as an instance of the class, or null if no record is found.
     */
    public function findOneBy(array $conditions,string $operator = 'AND'): ?self {
        $operator = strtoupper(trim($operator));
        if (!in_array($operator, ['AND', 'OR'])) {
            throw new EInvalidArgumentException("Invalid logical operator '$operator'");
        }

        $db = $this->getDatabase();
        if (! $db) {
            return null;
        }

        $whereClauses = implode(" $operator ", array_map(fn($key) => "$key = :$key", array_keys($conditions)));
        $sql          = "SELECT * FROM {$this->table} WHERE {$whereClauses} LIMIT 1";
        $data         = $db->setQuery($sql, $conditions)->fetch();
        if (! $data) {
            return null;
        }

        $this->data = new ModelData($data);
        $this->loadRelations($this->data);
        return $this;
    }

    /**
     * Retrieves all records from the database.
     *
     * @return array An array of all records.
     */
    public function findAll(): array {
        $db = $this->getDatabase();
        if (! $db) {
            return [];
        }

        $data    = $db->setQuery("SELECT * FROM {$this->getTable()}")->fetchAll([\PDO::FETCH_ASSOC]);
        $results = [];
        foreach ($data as $row) {
            $model       = new static($db);
            $model->data = new ModelData($row);
            $model->loadRelations($model->data);
            $results[] = $model;
        }

        return $results;
    }

    /**
     * Finds all records that match the given conditions.
     *
     * @param array $conditions An associative array of conditions where the key is the column name and the value is the value to match.
     * @return array An array of records that match the given conditions.
     */
    public function findAllBy(array $conditions,string $operator = 'AND'): array {
        $operator = strtoupper(trim($operator));
        if (!in_array($operator, ['AND', 'OR'])) {
            throw new EInvalidArgumentException("Invalid logical operator '$operator'");
        }
                
        $db = $this->getDatabase();
        if (! $db) {
            return [];
        }

        $whereClauses = implode(" $operator ", array_map(fn($key) => "$key = :$key", array_keys($conditions)));
        $sql          = "SELECT * FROM {$this->table} WHERE {$whereClauses}";
        $data         = $db->setQuery($sql, $conditions)->fetchAll([\PDO::FETCH_ASSOC]);
        $results      = [];
        foreach ($data as $row) {
            $model       = new static($db);
            $model->data = new ModelData($row);
            $model->loadRelations($model->data);

            $results[] = $model;
        }

        return $results;
    }

    /**
     * Loads the model with the provided input data.
     *
     * @param array $input An associative array of data to load into the model.
     * @return self Returns the instance of the model.
     */
    public function load(array $input): self {
        $relations = $this->getRelations();
        //TODO Добавить снятие слепка "до" и "после" загрузки, чтобы можно было фиксировать изменения
        if ($this->beforeLoad()) {
            foreach ($input as $key => $value) {
                if (array_key_exists($key, $relations)) {
                    $relationDefinition = $relations[$key];
                    $relationModel      = $relationDefinition['model'];
                    $relationType       = $relationDefinition['type'];

                    if ($relationType === 'hasMany' && is_array($value)) {
                        if ($this->$key && is_array($this->$key)) {
                            $existingItems = $this->$key;
                            $primaryKey    = (new $relationModel())->primaryKey;

                            $existingIds = array_map(fn($item) => $item->$primaryKey, $existingItems);
                            $newIds      = array_map(fn($item) => $item[$primaryKey] ?? null, $value);

                            foreach ($newIds as $newId) {
                                if ($newId !== null && ! in_array($newId, $existingIds)) {
                                    throw new EDatabaseError("Data inconsistency detected for 'hasMany' relation: `$key` (missing '$primaryKey': $newId)");
                                }
                            }

                            $this->$key = array_map(function ($item) use ($existingItems, $relationModel, $primaryKey) {
                                $itemId = $item[$primaryKey] ?? null;
                                foreach ($existingItems as $existingItem) {
                                    if ($existingItem->$primaryKey === $itemId) {
                                        $existingItem->load($item);
                                        return $existingItem;
                                    }
                                }
                                return (new $relationModel($this->getDatabase()))->load($item);
                            }, $value);
                        } else {
                            $this->$key = array_map(fn($item) => (new $relationModel($this->getDatabase()))->load($item), $value);
                        }
                    } elseif (($relationType === 'hasOne' || $relationType === 'belongsTo') && is_array($value)) {
                        if ($this->$key instanceof ModelAbstract) {
                            $primaryKey = $this->$key->primaryKey;
                            if (isset($value[$primaryKey]) && $value[$primaryKey] === $this->$key->$primaryKey) {
                                $this->$key->load($value);
                            } else {
                                throw new EDatabaseError("Data is not consistent with the current model data");
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

    /**
     * Inserts a new record into the database.
     *
     * @return int The ID of the newly inserted record.
     */
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

    /**
     * Updates the current record in the database.
     *
     * @return int The number of affected rows.
     */
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

    /**
     * Deletes the current record from the database.
     *
     * @param bool $cascade Whether to delete related records in a cascading manner.
     * @return int The number of affected rows.
     */
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

    /**
     * Checks if the current model instance exists in the database.
     *
     * @return bool True if the model instance exists, false otherwise.
     */
    public function isExisting(): bool {
        return $this->data->has($this->primaryKey);
    }

    /**
     * Get the ID of the model.
     *
     * @return mixed The ID of the model.
     */
    public function getId(): mixed {
        return $this->data->{$this->primaryKey};
    }

    /**
     * Retrieves the mode of the current model.
     *
     * @return string|null The mode of the model, or null if not set.
     */
    public function getMode(): ?string {
        return $this->mode;
    }

    /**
     * Sets the mode for the model.
     *
     * @param string $mode The mode to set.
     * @return self Returns the instance of the model.
     */
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

    /**
     * Checks if the given type is transactional.
     *
     * @param int $type The type to check.
     * @return bool True if the type is transactional, false otherwise.
     */
    public function isTransactional(int $type): bool {
        $mode         = $this->getMode();
        $transactions = $this->getTransactions();

        return isset($transactions[$mode]) && ($transactions[$mode] & $type);
    }

    /**
     * Retrieves the name of the table associated with the model.
     *
     * @return string The name of the table.
     */
    public function getTable(): string {
        if (! $this->table) {
            $this->table = strtolower((new \ReflectionClass($this))->getShortName());
        }

        return $this->table;
    }

    /**
     * Retrieves the primary key of the model.
     *
     * @return string The primary key of the model.
     */
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

    /**
     * Retrieves the relations associated with the model.
     *
     * @return array An array of relations.
     */
    protected function getRelations(): array {
        // return [
        //     'profile' => [
        //         'model'       => ModelClassA::class,
        //         'method'      => 'findByUserId',
        //         'primary_key' => 'id',
        //         'foreign_key' => 'user_id',
        //         'type'        => 'hasOne', // hasOne|hasMany|belongsTo
        //     ],
        //     'comments' => [
        //         'model'       => ModelClassB::class,
        //         'method'      => 'findByUserId',
        //         'primary_key' => 'id',
        //         'foreign_key' => 'user_id',
        //         'type'        => 'hasMany',
        //     ],
        // ];        
        return [];
    }

    public function toArray(): array {
        return $this->clearRelations($this->data->toArray());
    }

    /**
     * Loads the relations for the given model data.
     *
     * @param ModelData $modelData The model data for which to load relations.
     *
     * @return void
     */
    private function loadRelations(ModelData $modelData): void {
        foreach ($this->getRelations() as $relation => $config) {
            $relatedModel  = new $config['model']($this->getDatabase());
            $relatedMethod = $config['method'];
            $primaryKey    = $config['primary_key'];

            $this->$relation = $relatedModel->$relatedMethod($modelData->{$primaryKey});
        }
    }

    /**
     * Synchronizes the relations of the current model.
     *
     * @return array An array containing the synchronized relations.
     */
    private function syncRelations(): array {
        $result = [];
        foreach ($this->getRelations() as $relation => $config) {
            /** @var ModelAbstract|array $relatedData */
            $relatedData = $this->$relation ?? null;
            if ($relatedData === null) {
                continue;
            }

            $foreignKey = $config['foreign_key'];

            if (($relatedData instanceof ModelAbstract) && ($config['type'] === 'hasOne' || $config['type'] === 'belongsTo')) {
                $relatedData->$foreignKey = $this->{$this->primaryKey};
                if ($relatedData->{$relatedData->primaryKey}) {
                    $relatedData->update();
                    $result[$relation] = 'updated';
                } else {
                    $relatedData->insert();
                    $result[$relation] = 'inserted';
                }
            } elseif (is_array($relatedData) && $config['type'] === 'hasMany') {
                $updated  = 0;
                $inserted = 0;                
                foreach ($relatedData as $item) {
                    /** @var ModelAbstract $item */
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

    /**
     * Removes the relations associated with the current model.
     *
     * @return array An array containing the details of the removed relations.
     */
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