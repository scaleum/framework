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
    protected ?string $mode = null;

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
        if ($this->beforeLoad()) {
            foreach ($input as $key => $value) {
                if (array_key_exists($key, $relations)) {
                    $relationConfig = $relations[$key];
                    $relationModel  = new $relationConfig['model']($this->getDatabase());
                    $relationType   = $relationConfig['type'];

                    if ($relationType === 'hasMany' && is_array($value)) {
                        $this->$key = array_map(fn($item) => (new $relationConfig['model']($this->getDatabase()))->load($item), $value);
                    } elseif (($relationType === 'hasOne' || $relationType === 'belongsTo') && is_array($value)) {
                        if($this->$key !== null && $this->$key instanceof ModelAbstract) {
                            $this->$key->load($value);
                        } else {
                            // $this->$key = (new $relationConfig['model']($this->getDatabase()))->load($value);
                            $this->$key = $relationModel->load($value);
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

    public function insert(): bool {
        // if ($this->isExisting()) {
        //     return $this->update();
        // }

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

    public function insertInternal(): bool {
        $db = $this->getDatabase();
        if (! $db || empty($this->data->toArray())) {
            return false;
        }

        if ($this->beforeInsert()) {
            $data = $this->filterAttributes($this->data->toArray());
            $columns      = implode(", ", array_keys($data));
            $placeholders = ":" . implode(", :", array_keys($data));
            $result       = $db->setQuery("INSERT INTO {$this->getTable()} ({$columns}) VALUES ({$placeholders})", $data)->execute();
            if ($result) {
                $this->data->{$this->primaryKey} = $db->getLastInsertID();
            }
            $this->saveRelations();
            $this->afterInsert();            
            return $result > 0;
        }

        return false;
    }

    public function update(): bool {
        if (! $this->isExisting()) {
            return $this->insert();
        }

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

    public function updateInternal(): bool {
        $db = $this->getDatabase();
        if (! $db || ! $this->data->has($this->primaryKey)) {
            return false;
        }

        if ($this->beforeUpdate()) {
            $data   = $this->filterAttributes($this->data->toArray());
            $fields = implode(", ", array_map(fn($key) => "$key = :$key", array_keys($data)));
            $result = $db->setQuery("UPDATE {$this->getTable()} SET {$fields} WHERE {$this->primaryKey} = :{$this->primaryKey}", $data)->execute();
            
            $this->saveRelations();
            $this->afterUpdate();

            return $result > 0;
        }

        return false;
    }

    public function delete(bool $cascade = false): bool {
        if (! $this->isExisting()) {
            return false;
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

    public function deleteInternal(bool $cascade = false): bool {
        $db = $this->getDatabase();
        if (! $db || ! $this->data->has($this->primaryKey)) {
            return false;
        }

        if ($this->beforeDelete()) {
            $id = $this->data->{$this->primaryKey};

            // Если включено каскадное удаление, удаляем связанные модели
            if ($cascade) {
                $this->deleteRelations();
            }

            $result = $db->setQuery("DELETE FROM {$this->getTable()} WHERE {$this->primaryKey} = :id", ['id' => $id])->execute();
            $this->afterDelete();
            if ($result) {
                $this->data = new ModelData(); // Очищаем данные после удаления
            }
            return $result > 0;
        }

        return false;
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
     * The above declaration specifies that in the "admin" mode, the insert operation ([[insert()]])
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

    protected function getRelations(): array {
        return [];
    }

    private function loadRelations(ModelData $modelData): void {
        foreach ($this->getRelations() as $relation => $config) {
            $relatedModel  = new $config['model']($this->getDatabase());
            $relatedMethod = $config['method'];
            $primaryKey    = $config['primary_key'];

            $this->$relation = $relatedModel->$relatedMethod($modelData->{$primaryKey});
        }
    }

    private function saveRelations(): void {
        foreach ($this->getRelations() as $relation => $config) {
            // Проверяем, загружена ли связь (может быть null)
            $relatedData = $this->$relation ?? null;
            if ($relatedData === null) {
                continue; // Если связь не загружена, пропускаем
            }

            $foreignKey = $config['foreign_key'];

            if ($config['type'] === 'hasOne' || $config['type'] === 'belongsTo') {
                $relatedData->$foreignKey = $this->{$this->primaryKey}; // Проставляем внешний ключ

                if ($relatedData->{$relatedData->primaryKey}) {
                    $relatedData->update();
                } else {
                    $relatedData->insert();
                }
            } elseif ($config['type'] === 'hasMany') {
                foreach ($relatedData as $item) {
                    $item->$foreignKey = $this->{$this->primaryKey};

                    if ($item->{$item->primaryKey}) {
                        $item->update();
                    } else {
                        $item->insert();
                    }
                }
            }
        }
    }

    private function deleteRelations(): void {
        foreach ($this->getRelations() as $relation => $config) {
            if (! $this->data->has($relation)) {
                continue;
            }

            $relatedModel = new $config['model']($this->getDatabase());
            $relatedData  = $this->$relation;

            if ($config['type'] === 'hasOne' || $config['type'] === 'belongsTo') {
                $relatedModel->find($relatedData->{$relatedModel->primaryKey})?->delete();
            } elseif ($config['type'] === 'hasMany') {
                foreach ($relatedData as $item) {
                    $relatedModel->find($item->{$relatedModel->primaryKey})?->delete();
                }
            }
        }
    }

    private function filterAttributes(array $data): array {
        foreach ($this->getRelations() as $relation => $config) {
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

    /**
     * Get the value of data
     */
    public function getData() {
        return $this->data;
    }
}
/** End of ModelAbstract **/