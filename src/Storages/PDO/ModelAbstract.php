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

use Closure;
use Scaleum\Stdlib\Exceptions\EDatabaseError;
use Scaleum\Stdlib\Exceptions\ERuntimeError;
use Scaleum\Stdlib\Helpers\ArrayHelper;

/**
 * ModelAbstract
 *
 * @author Maxim Kirichenko <kirichenko.maxim@gmail.com>
 */
abstract class ModelAbstract extends DatabaseProvider implements ModelInterface {
    public const ON_INSERT   = 0x01;
    public const ON_UPDATE   = 0x02;
    public const ON_DELETE   = 0x04;
    public const ON_TRUNCATE = 0x08;

    protected ?self $parent  = null;
    protected ?string $mode  = null;
    protected ?string $table = null;
    protected ModelData $data;
    protected array $lastStatus  = [];
    protected string $primaryKey = 'id';

    /**
     * Explicit factories for subclasses(relations) if the standard _construct is not applicable.
     *
     * Example:
     * [ SpecialModel::class => fn() => new SpecialModel($this->getDatabase(), $this, 'extra'), ...]
     * @var array<class-string, callable>
     */
    protected array $relationFactories = [];

    public function __construct(?Database $database = null, ?self $parent = null) {
        parent::__construct($database);
        $this->parent = $parent;
        $this->data   = new ModelData();
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

        if (! $data = ($db->getQueryBuilder())->select()->from($this->getTable())->where($this->primaryKey, $id)->limit(1)->row()) {
            return null;
        }

        $this->setData(new ModelData($data));
        return $this;
    }

    /**
     * Finds a single record in the database that matches the given conditions.
     *
     * @param array|Closure $conditions An array of conditions or a Closure that defines the conditions for the query.
     * @param string $operator The logical operator to combine conditions (e.g., 'AND', 'OR'). Default is 'AND'.
     *
     * @return self|null Returns an instance of the model if a matching record is found, or null if no match is found.
     */
    public function findOneBy(array | Closure $conditions, string $operator = 'AND'): ?self {

        $db = $this->getDatabase();
        if (! $db) {
            return null;
        }

        $query = ($db->getQueryBuilder())->select()->from($this->getTable());
        if ($conditions instanceof Closure) {
            $conditions($query);
        } else {
            $method = strtoupper($operator) === 'OR' ? 'orWhere' : 'where';
            $query->$method($conditions);
        }

        if (! $data = $query->limit(1)->row()) {
            return null;
        }

        $this->setData(new ModelData($data));
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

        if (! $rows = ($db->getQueryBuilder())->select()->from($this->getTable())->rows([\PDO::FETCH_ASSOC])) {
            return [];
        }

        $results = [];
        foreach ($rows as $row) {
            $model = $this->createModelInstance(static::class);
            $model->setData(new ModelData($row));

            $results[] = $model;
        }

        return $results;
    }

    /**
     * Retrieves all records from the database that match the specified conditions.
     *
     * @param array|Closure $conditions An array of conditions or a Closure that defines the query logic.
     * @param string $operator The logical operator to combine conditions (e.g., 'AND', 'OR'). Default is 'AND'.
     * @return array An array of records that match the specified conditions.
     */
    public function findAllBy(array | Closure $conditions, string $operator = 'AND'): array {
        $results = [];
        $db      = $this->getDatabase();
        if (! $db) {
            return $results;
        }

        $query = ($db->getQueryBuilder())->select()->from($this->getTable());
        if ($conditions instanceof Closure) {
            $conditions($query);
        } else {
            $method = strtoupper($operator) === 'OR' ? 'orWhere' : 'where';
            $query->$method($conditions);
        }

        if (! $rows = $query->rows([\PDO::FETCH_ASSOC])) {
            return $results;
        }

        foreach ($rows as $row) {
            $model = $this->createModelInstance(static::class);
            $model->setData(new ModelData($row));

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
        $input     = ArrayHelper::naturalize($input);
        $relations = $this->getRelations();
        if ($this->beforeLoad($input)) {
            foreach ($input as $key => $value) {
                if (array_key_exists($key, $relations)) {
                    $relationDefinition = $relations[$key];
                    $relationModel      = $relationDefinition['model'];
                    $relationType       = $relationDefinition['type'];

                    if ($relationType === 'hasMany' && is_array($value)) {
                        if ($this->$key && is_array($this->$key)) {
                            $existingItems = $this->$key;
                            $primaryKey    = $this->createModelInstance($relationModel)->primaryKey;

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
                                return $this->createModelInstance($relationModel)->load($item);
                            }, $value);
                        } else {
                            $this->$key = array_map(fn($item) => $this->createModelInstance($relationModel)->load($item), $value);
                        }
                    } elseif ($relationType === 'hasOne' && is_array($value)) {
                        if ($this->$key instanceof ModelAbstract) {
                            $primaryKey = $this->$key->primaryKey;
                            if (isset($value[$primaryKey]) && $value[$primaryKey] === $this->$key->$primaryKey) {
                                $this->$key->load($value);
                            } else {
                                throw new EDatabaseError("Data is not consistent with the current model data");
                            }
                        } else {
                            $this->$key = $this->createModelInstance($relationModel)->load($value);
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
            // PK already set?
            $manualKey = $this->data->{$this->primaryKey} ?? null;

            $data   = $this->clearRelations($this->data->toArray());
            $result = (int) ($db->getQueryBuilder())->insert($this->getTable(), $data);

            // if $manualKey is null, we should use lastInsertId
            if ($manualKey === null) {
                try {
                    $lastId = $db->getLastInsertID();
                } catch (\PDOException $except) {
                    throw new EDatabaseError("Failed to get ID of inserted record: " . $except->getMessage());
                }

                if ($lastId === null) {
                    throw new EDatabaseError("Failed to determine PK after insert");
                }

                $this->data->{$this->primaryKey} = $lastId;
            }

            $relationResults = $this->updateRelations();

            $this->lastStatus = [
                'status'      => (bool) $result,
                'status_text' => $result ? 'Record inserted' : 'Record not inserted',
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
            $result           = ($db->getQueryBuilder())->set($data)->where($this->primaryKey, $this->{$this->primaryKey})->update($this->getTable());
            $relationResults  = $this->updateRelations();
            $this->lastStatus = [
                'status'      => (bool) $result,
                'status_text' => $result ? 'Record updated' : 'Record not updated',
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
        if (! $db || ! $this->data->hasAttribute($this->primaryKey)) {
            $this->lastStatus = [
                'status'      => (bool) $result,
                'status_text' => 'Database connection error or empty data',
            ];
            return $result;
        }

        if ($this->beforeDelete()) {
            $id              = $this->data->{$this->primaryKey};
            $relationResults = $cascade ? $this->removeRelations() : [];
            $result          = ($db->getQueryBuilder())->from($this->getTable())->where($this->primaryKey, $id)->delete();
            if ($result) {
                $this->setData(new ModelData());
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

    public function truncate(): int {
        if (! $this->isTransactional(self::ON_TRUNCATE)) {
            return $this->truncateInternal();
        }

        $this->getDatabase()->begin();
        try {
            $result = $this->truncateInternal();
            if ($result === false) {
                $this->getDatabase()->rollBack();
            } else {
                $this->getDatabase()->commit();
            }

            return $result;
        } catch (\Exception $except) {
            $this->getDatabase()->rollback();
            throw $except;
        }
    }

    protected function truncateInternal(): int {
        $this->lastStatus = [];
        $result           = 0;
        $db               = $this->getDatabase();

        if (! $db) {
            $this->lastStatus = [
                'status'      => (bool) $result,
                'status_text' => 'Database connection error or empty data',
            ];
            return $result;
        }

        if ($this->beforeTruncate()) {
            if ($query = $this->getDatabase()->getQueryBuilder()) {
                $result = $query->truncate($this->getTable());

                $this->lastStatus = [
                    'status'      => (bool) $result,
                    'status_text' => "Model table `{$this->getTable()}` was " . ($result ? 'truncated' : 'not truncated'),
                ];
                $this->afterTruncate();
                return $result;
            }
        }

        return $result;
    }
    /**
     * Checks if the current model instance exists in the database.
     *
     * @return bool True if the model instance exists, false otherwise.
     */
    public function isExisting(): bool {
        return $this->data->hasAttribute($this->primaryKey);
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
     * The above defines that in the "admin" mode, the insert operation ([[insert()]])
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
    public function getData(): ModelData {
        return $this->data;
    }

    public function setData(ModelData $data): void {
        $this->data = $data;
        if (! $this->data->isEmpty()) {
            $this->loadRelations();
        }
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
        //     'relation_1' => [
        //         'model'       => ModelClassA::class,
        //         'method'      => 'findByUserId',
        //         'primary_key' => 'id',
        //         'foreign_key' => 'user_id',
        //         'type'        => 'hasOne', // hasOne|hasMany|belongsTo
        //         'persist'      => true,
        //         // 'reference_key' => 'user_id', // not required, if the same as `foreign_key`
        //     ],
        //     'relation_2' => [
        //         'model'       => ModelClassB::class,
        //         'method'      => 'findByUserId',
        //         'primary_key' => 'id',
        //         'foreign_key' => 'user_id',
        //         'type'        => 'hasMany',
        //         'persist'      => true, // true|false; if true, the relation will be updated(insert/update/delete) in the database
        //         // 'reference_key' => 'user_id', // not required, if the same as `foreign_key`
        //     ],
        // ];
        return [];
    }

    /**
     * Get the value of parent
     */
    public function getParent(): ?self {
        return $this->parent;
    }

    /**
     * Converts the current model instance to an associative array.
     *
     * @param bool $strict Determines whether to strictly include only the properties
     *                     defined in the model. If true, only model-defined properties
     *                     will be included; if false, additional properties(relations) will be included.
     * @return array An associative array representation of the model instance.
     */
    public function toArray(bool $strict = true): array {
        $result = $this->data->toArray();
        if ($strict) {
            $result = $this->clearRelations($result);
        }
        return $result;
    }

    /**
     * Loads the relations for the given model data.
     *
     * @return void
     */
    private function loadRelations(): void {
        foreach ($this->getRelations() as $relation => $config) {
            $model           = $this->createModelInstance($config['model']);
            $method          = $config['method'];
            $referenceKey    = $config['reference_key'] ?? $this->primaryKey;
            $this->$relation = $model->{$method}($this->data->{$referenceKey});
        }
    }

    /**
     * Creates and returns an instance of a (related) model.
     *
     * @template T of ModelAbstract
     * @param class-string<T> $modelClass
     * @return T
     */
    protected function createModelInstance(string $modelClass) {
        if (isset($this->relationFactories[$modelClass])) {
            $factory  = $this->relationFactories[$modelClass];
            $instance = $factory();
        } else {
            $selfCtr     = (new \ReflectionClass($this))->getConstructor();
            $targetClass = new \ReflectionClass($modelClass);
            $targetCtr   = $targetClass->getConstructor();

            $args = [];

            if ($selfCtr && $targetCtr) {
                $selfParams   = $selfCtr->getParameters();
                $targetParams = $targetCtr->getParameters();

                foreach ($targetParams as $i => $param) {
                    $arg = null;
                    if (isset($selfParams[$i])) {
                        $name = $selfParams[$i]->getName();
                        if (property_exists($this, $name)) {
                            $arg = $this->$name;
                        } elseif (method_exists($this, 'get' . ucfirst($name))) {
                            $arg = $this->{'get' . ucfirst($name)}();
                        } elseif ($selfParams[$i]->isDefaultValueAvailable()) {
                            $arg = $selfParams[$i]->getDefaultValue();
                        } else {
                            throw new ERuntimeError("Unknown parameter `$name` in `{$modelClass}::__construct()`");
                        }
                    } elseif ($param->isDefaultValueAvailable()) {
                        $arg = $param->getDefaultValue();
                    } else {
                        throw new ERuntimeError("Cannot resolve parameter in `{$modelClass}::__construct()`");
                    }

                    $args[] = $arg;
                }
            }

            $instance = $targetClass->newInstanceArgs($args);
        }

        return $instance;
    }

    /**
     * Synchronizes the relations of the current model.
     *
     * @return array An array containing the synchronized relations.
     */
    private function updateRelations(): array {
        $result = [];
        foreach ($this->getRelations() as $relation => $config) {
            // if the persist option is set to false, skip this relation
            if (isset($config['persist']) && $config['persist'] === false) {
                continue;
            }

            /** @var ModelAbstract|array $relatedData */
            $relatedData = $this->$relation ?? null;
            if ($relatedData === null) {
                continue;
            }

            $foreignKey   = $config['foreign_key'];
            $referenceKey = $config['reference_key'] ?? $this->primaryKey;

            if (($relatedData instanceof ModelAbstract) && $config['type'] === 'hasOne') {
                $relatedData->$foreignKey = $this->data->{$referenceKey};
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
                    $item->$foreignKey = $this->data->{$referenceKey};
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
            // if the persist option is set to false, skip this relation
            if (isset($config['persist']) && $config['persist'] === false) {
                continue;
            }

            $relatedData = $this->$relation ?? null;
            if ($relatedData === null) {
                continue;
            }

            if ($config['type'] === 'hasOne') {
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
            if (array_key_exists($relation, $data)) {
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
     * What should I do after truncating?
     * @return mixed
     */
    protected function afterTruncate() {

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
    protected function beforeLoad(array &$input) {
        return true;
    }

    /**
     * What should I do before updating? You must always return a Boolean value
     * @return bool [FALSE|TRUE]
     */
    protected function beforeUpdate() {
        return true;
    }

    /**
     * What should I do before truncating? You must always return a Boolean value
     * @return bool [FALSE|TRUE]
     */
    protected function beforeTruncate() {
        return true;
    }
}
/** End of ModelAbstract **/