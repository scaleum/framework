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

namespace Scaleum\Storages\PDO\Builders;

use Scaleum\Stdlib\Exceptions\EDatabaseError;
use Scaleum\Storages\PDO\Builders\Contracts\IndexBuilderInterface;
use Scaleum\Storages\PDO\Database;

/**
 * IndexBuilder
 *
 * @author Maxim Kirichenko <kirichenko.maxim@gmail.com>
 */
class IndexBuilder extends BuilderAbstract implements IndexBuilderInterface {
    public const TYPE_PK       = 'primary';
    public const TYPE_INDEX    = 'index';
    public const TYPE_UNIQUE   = 'unique';
    public const TYPE_FULLTEXT = 'fulltext';
    public const TYPE_FOREIGN  = 'foreign';

    public const ACTION_CASCADE     = 'CASCADE';
    public const ACTION_SET_NULL    = 'SET NULL';
    public const ACTION_SET_DEFAULT = 'SET DEFAULT';
    public const ACTION_RESTRICT    = 'RESTRICT';
    public const ACTION_NO_ACTION   = 'NO ACTION';

    protected array $tableTypes = [
        self::TYPE_PK       => 'PRIMARY KEY',
        self::TYPE_INDEX    => 'INDEX',
        self::TYPE_UNIQUE   => 'UNIQUE',
        self::TYPE_FULLTEXT => 'FULLTEXT',
        self::TYPE_FOREIGN  => 'FOREIGN KEY',
    ];

    protected array $tableActions = [
        self::ACTION_CASCADE,
        self::ACTION_SET_NULL,
        self::ACTION_SET_DEFAULT,
        self::ACTION_RESTRICT,
        self::ACTION_NO_ACTION,
    ];

    protected static array $adapters = [
        'mysql'  => Adapters\MySQL\Index::class,
        'pgsql'  => Adapters\PostgreSQL\Index::class,
        'sqlite' => Adapters\SQLite\Index::class,
        'sqlsrv' => Adapters\SQLServer\Index::class,
        'mssql'  => Adapters\SQLServer\Index::class,
    ];
    protected array $columns        = [];
    protected array $columnsForeign = [];
    protected ?string $indexName    = null;
    protected string $type          = self::TYPE_INDEX;
    protected ?string $table        = null;
    protected ?string $tableForeign = null;
    protected ?string $onDelete     = null;
    protected ?string $onUpdate     = null;

    public function __construct(string $type = self::TYPE_INDEX, array | string | null $column = null, ?string $indexName = null, ?string $table = null, ?Database $database = null) {
        // check if type is supported
        if (! $this->mapType($type)) {
            throw new EDatabaseError(sprintf('Index type `%s` is not supported', strtoupper($type)));
        }
        $this->type = $type;

        if ($column !== null) {
            $this->column($column);
        }

        if ($indexName !== null) {
            $this->name($indexName);
        }

        if ($table !== null) {
            $this->table($table);
        }

        parent::__construct($database);
    }

    public function reference(string $table, array | string $column, ?string $actionOnDelete = null, ?string $actionOnUpdate = null): self {
        $this->tableForeign = $table;

        if (is_string($column)) {
            $column = [$column];
        }

        foreach ($column as $columnEntry) {
            if (! in_array($columnEntry, $this->columnsForeign)) {
                $this->columnsForeign[] = $columnEntry;
            }
        }

        if ($actionOnDelete !== null) {
            $this->onDelete($actionOnDelete);
        }

        if ($actionOnUpdate !== null) {
            $this->onUpdate($actionOnUpdate);
        }

        return $this;
    }

    public function column(array | string $column): self {
        if (is_string($column)) {
            $column = [$column];
        }

        foreach ($column as $columnEntry) {
            if (! in_array($columnEntry, $this->columns)) {
                $this->columns[] = $columnEntry;
            }
        }

        return $this;
    }

    public function name(string $indexName): self {
        if (! empty($indexName) && $this->indexName != $indexName) {
            $this->indexName = $indexName;
        }

        return $this;
    }

    public function table(string $table): self {
        $this->table = $table;
        return $this;
    }

    public function makeSQL(): string {
        if (empty($this->columns)) {
            throw new EDatabaseError('Column information is required for that operation.');
        }

        return match ($this->type) {
            static::TYPE_FOREIGN => $this->makeForeignKey(),
            static::TYPE_FULLTEXT => $this->makeFulltext(),
            static::TYPE_INDEX => $this->makeIndex(),
            static::TYPE_PK => $this->makePrimaryKey(),
            static::TYPE_UNIQUE => $this->makeUnique(),
        };
    }
    ////////////////////////////////////////////////////////////////////////////
    protected function onDelete(string $action): self {
        $action = strtoupper($action);
        if (! in_array($action, $this->tableActions)) {
            throw new EDatabaseError(sprintf('Unsupported ON DELETE action: %s', $action));
        }
        $this->onDelete = $action;
        return $this;
    }

    protected function onUpdate(string $action): self {
        $action = strtoupper($action);
        if (! in_array($action, $this->tableActions)) {
            throw new EDatabaseError(sprintf('Unsupported ON UPDATE action: %s', $action));
        }
        $this->onUpdate = $action;
        return $this;
    }

    protected function mapType(string $type): ?string {
        return $this->tableTypes[$type] ?? null;
    }

    protected function makePrimaryKey(): string {
        $result = '';
        if ($this->table !== null) {
            $result .= "ALTER TABLE " . $this->protectIdentifiers($this->table) . " ADD ";
        }

        $column    = implode(',', $this->protectIdentifiers($this->columns));
        $indexName = $this->protectIdentifiers($this->indexName ?? "pk_" . implode('_', $this->columns));

        $result .= "CONSTRAINT $indexName PRIMARY KEY ($column)";
        return $result;
    }

    protected function makeUnique(): string {
        $column    = implode(',', $this->protectIdentifiers($this->columns));
        $indexName = $this->protectIdentifiers($this->indexName ?? "unq_" . implode('_', $this->columns));
        if ($this->table !== null) {
            return "CREATE UNIQUE INDEX $indexName ON {$this->protectIdentifiers($this->table)} ($column)";
        } else {
            return "CONSTRAINT $indexName UNIQUE ($column)";
        }
    }

    protected function makeForeignKey(): string {
        if (! $this->tableForeign) {
            throw new EDatabaseError('Foreign key requires a referenced table');
        }
        $result = '';
        if ($this->table !== null) {
            $result .= "ALTER TABLE " . $this->protectIdentifiers($this->table) . " ADD ";
        }

        $indexName = $this->protectIdentifiers($this->indexName ?? "fk_" . implode('_', $this->columns));
        $column    = implode(',', $this->protectIdentifiers($this->columns));
        $refTable  = $this->protectIdentifiers($this->tableForeign);
        $refColumn = implode(',', $this->protectIdentifiers($this->columnsForeign));

        $result .= "CONSTRAINT $indexName FOREIGN KEY ($column) REFERENCES $refTable($refColumn)";

        if ($this->onDelete) {
            $result .= " ON DELETE " . strtoupper($this->onDelete);
        }

        if ($this->onUpdate) {
            $result .= " ON UPDATE " . strtoupper($this->onUpdate);
        }

        return $result;
    }

    protected function makeFulltext(): string {
        $indexName = $this->protectIdentifiers($this->indexName ?? "ft_" . implode('_', $this->columns));
        $column    = implode(',', $this->protectIdentifiers($this->columns));
        if ($this->table !== null) {
            return "CREATE FULLTEXT INDEX $indexName ON {$this->protectIdentifiers($this->table)} ($column)";
        } else {
            return "FULLTEXT $indexName ($column)";
        }
    }

    protected function makeIndex(): string {
        $indexName = $this->protectIdentifiers($this->indexName ?? "key_" . implode('_', $this->columns));
        $column    = implode(',', $this->protectIdentifiers($this->columns));
        if ($this->table !== null) {
            return "CREATE INDEX $indexName ON {$this->protectIdentifiers($this->table)} ($column)";
        } else {
            return "INDEX $indexName ($column)";
        }
    }
}
/** End of IndexBuilder **/