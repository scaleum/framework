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

use Scaleum\Stdlib\Helpers\ArrayHelper;

/**
 * RecordsetAbstract
 *
 * @author Maxim Kirichenko <kirichenko.maxim@gmail.com>
 */
abstract class RecordsetAbstract extends DatabaseProvider implements \IteratorAggregate {
    protected array $records        = [];
    protected array $removed        = [];
    protected int $recordCount      = 0;
    protected int $recordTotalCount = 0;
    protected array $params         = [];
    protected ?string $modelClass   = null;

    public function __construct(?Database $database = null, ?string $modelClass = null, array $records = []) {
        parent::__construct($database);

        $this->modelClass = $modelClass;
        if ($modelClass !== null) {
            if (! class_exists($modelClass)) {
                throw new \RuntimeException("Model class `$modelClass` does not exist");
            }
        }

        $this->setRecords($records);
    }

    /**
     * Loads records from the database.
     *
     * @return static Returns the current instance of the class.
     */
    public function load(): static {
        $records                = $this->getDatabase()->setQuery($this->getQuery(), $this->getParams())->fetchAll([\PDO::FETCH_ASSOC]);
        $this->recordCount      = count($records);
        $this->recordTotalCount = $this->getQueryTotalCount();

        return $this->setRecords($records);
    }

    public function getRecords(): array {
        return $this->records;
    }

    public function setRecords(array $records): static {
        $this->reset();
        $this->records = $this->hydrateModels($records);
        return $this;
    }

    public function getParams(): array {
        return $this->params;
    }

    public function setParams(array $params): static {
        $this->params = $params;
        return $this;
    }

    /**
     * Retrieves an array of removed records.
     *
     * @return array An array containing the removed records.
     */
    public function getRemoved(): array {
        return $this->removed;
    }

    /**
     * Retrieves the number of records.
     *
     * @return int The count of records.
     */
    public function getRecordCount(): int {
        return $this->recordCount;
    }

    /**
     * Retrieves the total count of records(based on last query).
     *
     * @return int The total number of records.
     */
    public function getRecordTotalCount(): int {
        return $this->recordTotalCount;
    }

    /**
     * Returns an iterator for the records.
     *
     * This method provides an \ArrayIterator instance that can be used to iterate
     * over the records in the recordset.
     *
     * @return \ArrayIterator An iterator for the records.
     */
    public function getIterator(): \ArrayIterator {
        return new \ArrayIterator($this->records);
    }

    /**
     * Filters the recordset using a callback function.
     *
     * This method applies the given callback function to each element in the recordset
     * and returns a new instance of the recordset containing only the elements for which
     * the callback function returns true.
     *
     * Ex.1
     * $filteredRecordset = $recordset->filter(function($record) {
     *  return $record->status === 'active';
     * });
     *
     * Ex.2
     * $filteredRecordset = $recordset->filter(function($record) {
     *  return $record->age >= 18 && $record->age <= 30;
     * });
     *
     * Ex.3
     * $filteredRecordset = $recordset->filter(function($record) {
     *  return isset($record->email);
     * });
     *
     *
     * @param callable $callback The callback function to use for filtering. The function
     *                           should accept a single argument (the current element) and
     *                           return a boolean value.
     * @return static A new instance of the recordset containing only the elements that
     *              satisfy the callback function.
     */
    public function filter(callable $callback): static {
        $filtered = array_filter($this->records, $callback);
        return new static($this->getDatabase(), $this->modelClass, array_values($filtered));
    }

    /**
     * Adds a model to the recordset.
     *
     * @param array|ModelInterface $record The record to add.
     * @return static Returns the instance of the recordset.
     */
    public function add(mixed $record): static {
        if ((is_array($record) && ArrayHelper::isAssociative($record))) {
            // If modelClass predefined, hydrate the record
            if ($this->modelClass !== null) {
                $record = $this->hydrateModel($record, $this->modelClass);
            }            
            $this->records[] = $record;
        } elseif ($record instanceof ModelInterface) {
            // If modelClass not predefined, convert the model to array
            if($this->modelClass === null){
                $record = $record->toArray();
            }
            $this->records[] = $record;
        } else {
            throw new \InvalidArgumentException("Invalid record type, only associative arrays or ModelInterface instances are allowed");
        }

        return $this;
    }

    /**
     * Removes the given model from the recordset.
     *
     * @param ModelInterface $model The model to be removed.
     * @return static Returns the instance of the recordset for method chaining.
     */
    public function remove(ModelInterface $model): static {
        $primaryKey = $model->getPrimaryKey();
        foreach ($this->records as $index => $record) {
            if (! ($record instanceof ModelInterface) || get_class($record) !== get_class($model)) {
                continue;
            }

            if ($record->$primaryKey === $model->$primaryKey) {
                $this->removed[] = $record;
                unset($this->records[$index]);
                break;
            }
        }
        return $this;
    }

    /**
     * Removes an record from the recordset by its index.
     *
     * @param int $index The index of the record to be removed.
     * @return static Returns the current instance for method chaining.
     */
    public function removeByIndex(int $index): static {
        if (isset($this->records[$index])) {
            $this->removed[] = $this->records[$index];
            unset($this->records[$index]);
        }
        return $this;
    }

    /**
     * Saves the current recordset.
     *
     * This method is responsible for persisting the current state of the recordset
     * to the database. It does not return any value.
     *
     * @return void
     */
    public function save(): void {
        foreach ($this->removed as $model) {
            if ($model instanceof ModelInterface) {
                // Model will be deleted only if it exists in the database
                $model->delete();
            }
        }

        foreach ($this->records as $model) {
            if ($model instanceof ModelInterface) {
                // Model can herself decide to update or insert
                $model->update();
            }
        }

        $this->removed = [];
    }

    /**
     * Converts the recordset to an array.
     *
     * @return array The recordset as an array.
     */
    public function toArray(): array {
        return array_map(function ($item) {
            if ($item instanceof ModelInterface) {
                return $item->toArray();
            }
            return $item; // If it's not a model, return as is
        }, $this->records);
    }

    public function reset() {
        $this->removed = [];
        $this->records = [];
    }

    /**
     * Get the SQL query string.
     *
     * This abstract method should be implemented by subclasses to return
     * the SQL query string that will be used for database operations.
     *
     * @return string The SQL query string.
     */
    abstract protected function getQuery(): string;

    /**
     * Retrieves the total count of records for the current query.
     * Can be overridden by subclasses to provide a custom implementation.
     * The default implementation simply counts the records in the query result.
     *
     * @return int The total count of records.
     */
    protected function getQueryTotalCount(): int {
        $query = "SELECT COUNT(*) AS total FROM (" . preg_replace('/\s+LIMIT\s+(\d+|:\w+)(\s*(,|OFFSET)\s*(\d+|:\w+))?\s*$/i', '', $this->getQuery()) . ") AS SUB_QUERY";
        return (int) $this->getDatabase()->setQuery($query, $this->getParams())->fetchColumn();
    }

    /**
     * Hydrates an array of records into array of models.
     *
     * @param array $records An array of records to be hydrated.
     * @return array An array of hydrated models.
     */
    protected function hydrateModels(array $records): array {
        $modelClass = $this->modelClass;
        if ($modelClass !== null) {
            $records = array_map(function ($record) use ($modelClass) {
                if (ArrayHelper::isAssociative($record)) {
                    return $this->hydrateModel($record, $modelClass);
                }
                return $record;
            }, $records);
        }
        return $records;
    }

    protected function hydrateModel(array $record, string $modelClass): ModelInterface {
        if (! class_exists($modelClass)) {
            throw new \RuntimeException("Model class `$modelClass` does not exist");
        }
        return (new $modelClass($this->getDatabase()))->load($record);
    }
}
/** End of RecordsetAbstract **/