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
 * RecordsetAbstract
 *
 * @author Maxim Kirichenko <kirichenko.maxim@gmail.com>
 */
abstract class RecordsetAbstract extends DatabaseProvider implements \IteratorAggregate {
    protected array $records        = [];
    protected array $removed        = [];
    protected int $recordCount      = 0;
    protected int $recordTotalCount = 0;
    protected int $position         = 0;
    protected array $params         = [];
    protected int $pageSize         = 0;
    protected int $page             = 0;

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
     * Get the fully qualified class name of the model associated with the recordset.
     *
     * This abstract method should be implemented by subclasses to return the
     * fully qualified class name of the model that the recordset represents.
     *
     * @return string The fully qualified class name of the model.
     */
    abstract protected function getModelClass(): string;

    public function __construct(?Database $database = null, array $records = []) {
        parent::__construct($database);
        $this->setRecords($records);
    }

    public function flush() {
        $this->position = 0;
        $this->records  = [];
    }

    /**
     * Loads records from the database.
     *
     * @return static Returns the current instance of the class.
     */
    public function loadRecords(): static {
        $sql = $this->getQuery();
        // $countQuery = preg_replace('/SELECT\s+.*?\s+FROM\s+/i', 'SELECT COUNT(*) FROM ', $query);
        $countSql               = "SELECT COUNT(*) AS total FROM (" . preg_replace('/\s+LIMIT\s+\d+(\s*,\s*\d+)?$/i', '', $sql) . ") AS SUB_QUERY";
        $this->recordTotalCount = (int) $this->getDatabase()->setQuery($countSql, $this->getParams())->fetchColumn();

        $rows              = $this->getDatabase()->setQuery($sql, $this->getParams())->fetchAll([\PDO::FETCH_ASSOC]);
        $this->recordCount = count($rows);

        $modelClass = $this->getModelClass();
        if (! class_exists($modelClass)) {
            throw new \Exception("Model class `$modelClass` not found");
        }

        return $this->setRecords(array_map(fn($row) => (new $modelClass($this->getDatabase()))->load($row), $rows));
    }

    public function fetchRecord(): ModelInterface | null {
        if (($index = key($this->records)) !== null) {
            $this->position = $index;
            $result         = $this->getRecord();
            $this->nextRecord();

            return $result;
        }

        return null;
    }

    public function firstRecord(): ModelInterface | false {
        return reset($this->records);
    }

    public function hasRecord($index): bool {
        return isset($this->records[$index]);
    }

    public function lastRecord(): ModelInterface | false {
        return end($this->records);
    }

    public function nextRecord(): ModelInterface | false {
        return next($this->records);
    }

    public function prevRecord(): ModelInterface | false {
        return prev($this->records);
    }

    public function getRecord($index = null): ModelInterface {
        if ($index !== null) {
            if ($this->hasRecord($index)) {
                return $this->records[$index];
            }
        }

        return current($this->records);
    }

    public function getRecords() {
        return $this->records;
    }

    public function getPosition():int {
        return $this->position;
    }

    public function setRecords(array $records): static {
        $this->flush();
        $this->records = $records;

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
     * Get the value of pageSize
     */
    public function getPageSize(): int {
        return $this->pageSize;
    }

    /**
     * Set the value of pageSize
     *
     * @return  self
     */
    public function setPageSize(int $pageSize): static {
        $this->pageSize = $pageSize;
        return $this;
    }

    /**
     * Get the value of page
     */
    public function getPage(): int {
        return $this->page;
    }

    /**
     * Set the value of page
     *
     * @return  self
     */
    public function setPage(int $page): static {
        $this->page = $page;
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
        return new static($this->getDatabase(), array_values($filtered));
    }

    /**
     * Adds a model to the recordset.
     *
     * @param ModelInterface $model The model to add.
     * @return static Returns the instance of the recordset.
     */
    public function add(ModelInterface $model): static {
        $this->records[] = $model;
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
            if ($record->$primaryKey === $model->$primaryKey) {
                $this->removed[] = $record;
                unset($this->records[$index]);
                break;
            }
        }
        return $this;
    }

    /**
     * Removes an item from the recordset by its index.
     *
     * @param int $index The index of the item to be removed.
     * @return static Returns the current instance for method chaining.
     */
    public function removeBy(int $index): static {
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
            $model->delete();
        }

        foreach ($this->records as $model) {
            if (! empty($model->{$model->getPrimaryKey()})) {
                $model->update();
            } else {
                $model->insert();
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
        return array_map(fn($model) => $model->toArray(), $this->records);
    }
}
/** End of RecordsetAbstract **/