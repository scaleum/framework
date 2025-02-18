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
    public const TYPE_KEY      = 'index';
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
        self::TYPE_KEY      => 'KEY',
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
        'oci'    => Adapters\Oracle\Index::class,
    ];
    protected array $columns        = [];
    protected ?string $indexName    = null;
    protected string $type          = self::TYPE_KEY;
    protected ?string $refTableName = null;
    protected array $refColumns     = [];
    protected ?string $onDelete     = null;
    protected ?string $onUpdate     = null;

    public function __construct(string $type = self::TYPE_KEY, ?Database $database = null) {
        parent::__construct($database);

        // check if type is supported
        if (! $this->mapType($type)) {
            throw new EDatabaseError(sprintf('Index type `%s` is not supported', strtoupper($type)));
        }
        $this->type = $type;
    }

    public function reference(string $tableName, array | string $column): self {
        $this->refTableName = $tableName;

        if (is_string($column)) {
            $column = [$column];
        }

        foreach ($column as $columnEntry) {
            if (! in_array($columnEntry, $this->refColumns)) {
                $this->refColumns[] = $columnEntry;
            }
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

    public function name(string $index_name): self {
        if (! empty($index_name) && $this->indexName != $index_name) {
            $this->indexName = $index_name;
        }

        return $this;
    }

    public function onDelete(string $action): self {
        $action = strtoupper($action);
        if (! in_array($action, $this->tableActions)) {
            throw new EDatabaseError(sprintf('Unsupported ON DELETE action: %s', $action));
        }
        $this->onDelete = $action;
        return $this;
    }

    public function onUpdate(string $action): self {
        $action = strtoupper($action);
        if (! in_array($action, $this->tableActions)) {
            throw new EDatabaseError(sprintf('Unsupported ON UPDATE action: %s', $action));
        }
        $this->onUpdate = $action;
        return $this;
    }

    public function makeArray(): array {
        return [
            'type'       => $this->type,
            'column'     => $this->columns,
            'index_name' => $this->indexName,
        ];
    }

    public function makeString(): string {
        if (empty($this->columns)) {
            throw new EDatabaseError('Column information is required for that operation.');
        }

        return match ($this->type) {
            static::TYPE_PK => $this->makePrimaryKey(),
            static::TYPE_UNIQUE => $this->makeUnique(),
            static::TYPE_FOREIGN => $this->makeForeignKey(),
            static::TYPE_FULLTEXT => $this->makeFulltext(),
            default => $this->makeKey(),
        };
    }

    protected function mapType(string $type): ?string {
        return $this->tableTypes[$type] ?? null;
    }

    protected function makePrimaryKey(): string {
        $column = implode(',', $this->protectIdentifiers($this->columns));
        return "PRIMARY KEY ($column)";
    }

    protected function makeUnique(): string {
        $column    = implode(',', $this->protectIdentifiers($this->columns));
        $indexName = $this->protectIdentifiers($this->indexName);
        return ($indexName ? "CONSTRAINT $indexName " : "") . "UNIQUE ($column)";
    }

    protected function makeForeignKey(): string {
        if (! $this->refTableName) {
            throw new EDatabaseError('Foreign key requires a referenced table');
        }

        $indexName = $this->protectIdentifiers($this->indexName);
        $column    = implode(',', $this->protectIdentifiers($this->columns));
        $refTable  = $this->protectIdentifiers($this->refTableName);
        $refColumn = implode(',', $this->protectIdentifiers($this->refColumns));

        $str = ($indexName ? "CONSTRAINT $indexName " : "") . "FOREIGN KEY ($column) REFERENCES $refTable($refColumn)";

        if ($this->onDelete) {
            $str .= " ON DELETE " . strtoupper($this->onDelete);
        }

        if ($this->onUpdate) {
            $str .= " ON UPDATE " . strtoupper($this->onUpdate);
        }

        return $str;
    }

    protected function makeFulltext(): string {
        $indexName = $this->protectIdentifiers($this->indexName);
        $column    = implode(',', $this->protectIdentifiers($this->columns));
        return "FULLTEXT " . ($indexName ? "$indexName " : "") . "($column)";
    }

    protected function makeKey(): string {
        if (! $type = $this->mapType($this->type)) {
            throw new EDatabaseError(sprintf('Index type `%s` is not supported', strtoupper($this->type)));
        }
        $indexName = $this->protectIdentifiers($this->indexName);
        $column    = implode(',', $this->protectIdentifiers($this->columns));

        return strtoupper($type) . " " . ($indexName ? "$indexName " : "") . "($column)";
    }
}
/** End of IndexBuilder **/