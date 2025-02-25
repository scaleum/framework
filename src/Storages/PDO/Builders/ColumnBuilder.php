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
use Scaleum\Storages\PDO\Builders\Contracts\ColumnBuilderInterface;
use Scaleum\Storages\PDO\Database;

/**
 * ColumnBuilder
 *
 * @author Maxim Kirichenko <kirichenko.maxim@gmail.com>
 */
class ColumnBuilder extends BuilderAbstract implements ColumnBuilderInterface {
    public const TYPE_PK          = 'pk';
    public const TYPE_BIGPK       = 'bigpk';
    public const TYPE_STRING      = 'string';
    public const TYPE_TEXT        = 'text';
    public const TYPE_MEDIUM_TEXT = 'mediumtext';
    public const TYPE_LONG_TEXT   = 'longtext';
    public const TYPE_TINYINT     = 'tinyint';
    public const TYPE_SMALLINT    = 'smallint';
    public const TYPE_INTEGER     = 'integer';
    public const TYPE_BIGINT      = 'bigint';
    public const TYPE_FLOAT       = 'float';
    public const TYPE_DOUBLE      = 'double';
    public const TYPE_DECIMAL     = 'decimal';
    public const TYPE_DATETIME    = 'datetime';
    public const TYPE_TIMESTAMP   = 'timestamp';
    public const TYPE_TIME        = 'time';
    public const TYPE_DATE        = 'date';
    public const TYPE_BINARY      = 'binary';
    public const TYPE_BOOLEAN     = 'boolean';
    public const TYPE_MONEY       = 'money';
    public const TYPE_JSON        = 'json';

    public const MODE_CREATE = 2;
    public const MODE_ADD    = 4;
    public const MODE_UPDATE = 8;
    public const MODE_DROP   = 16;

    public const MODES = [
        self::MODE_CREATE => 'CREATE COLUMN',
        self::MODE_ADD    => 'ADD COLUMN',
        self::MODE_UPDATE => 'UPDATE COLUMN',
        self::MODE_DROP   => 'DROP COLUMN',
    ];

    private int $mode              = self::MODE_CREATE;
    protected ?string $table       = null;
    protected array $tableTypes    = [];
    protected array $tableDefaults = [];
    protected ?string $column      = null;
    protected ?string $comment     = null;

    /**
     * @var mixed column size or precision definition. This is what goes into the parenthesis after
     * the column type. This can be either a string, an integer or an array. If it is an array, the array values will
     * be joined into a string separated by comma.
     */
    protected mixed $constraint;
    /**
     * @var mixed default value of the column.
     */
    protected mixed $default = null;
    /**
     * @var boolean whether the column is not nullable. If this is `true`, a `NOT NULL` constraint will be added.
     */
    protected bool $isNotNull = false;
    /**
     * @var boolean whether the column values should be unique. If this is `true`, a `UNIQUE` constraint will be added.
     */
    protected bool $isUnique = false;
    /**
     * @var bool for column type definition such as INTEGER, SMALLINT, etc.
     */
    protected bool $isUnsigned = false;
    /**
     * @var string the column type definition such as INTEGER, VARCHAR, DATETIME, etc.
     */
    protected string $type = 'string';

    protected static array $adapters = [
        'mysql'  => Adapters\MySQL\Column::class,
        'pgsql'  => Adapters\PostgreSQL\Column::class,
        'sqlite' => Adapters\SQLite\Column::class,
        'sqlsrv' => Adapters\SQLServer\Column::class,
        'mssql'  => Adapters\SQLServer\Column::class,
    ];

    public function __construct(string $type = self::TYPE_STRING, mixed $constraint = null, ?Database $database = null) {
        // check if type is supported
        if (! $this->mapType($type)) {
            throw new EDatabaseError(sprintf('Column type `%s` is not supported', strtoupper($type)));
        }
        $this->type       = $type;
        $this->constraint = $constraint;

        parent::__construct($database);
    }

    public function setComment(string $str): self {
        $this->comment = $str;
        return $this;
    }

    public function getComment(): ?string {
        return $this->comment;
    }

    public function setDefaultValue(mixed $default, bool $quoted = true): self {
        if (is_string($default)) {
            $default = str_replace("'", "\\'", $default);
        }
        $this->default = $quoted == true ? "'$default'" : $default;

        return $this;
    }

    public function getDefaultValue(): mixed {
        return $this->default;
    }

    public function setColumn(string $str): self {
        $this->column = $str;

        return $this;
    }

    public function getColumn(): ?string {
        return $this->column;
    }

    public function setNotNull(bool $val = true): self {
        $this->isNotNull = $val;

        return $this;
    }

    public function getNonNull(): bool {
        return $this->isNotNull;
    }

    public function setUnique(bool $val = true): self {
        $this->isUnique = $val;

        return $this;
    }

    public function getUnique(): bool {
        return $this->isUnique;
    }

    public function setUnsigned(bool $val = true): self {
        $this->isUnsigned = $val;

        return $this;
    }

    public function getUnsigned(): bool {
        return $this->isUnsigned;
    }

    public function setTable(string $table): self {
        $this->table = $table;
        return $this;
    }

    public function getTable(): ?string {
        return $this->table;
    }

    public function setTableMode(int $mode): self {
        $this->mode = $mode;
        return $this;
    }

    public function getTableMode(): int {
        return $this->mode;
    }

    public function getTableModeName() {
        if (! isset(self::MODES[$this->mode])) {
            throw new EDatabaseError('Unsupported mode');
        }
        return self::MODES[$this->mode];
    }

    protected function mapDefault(string $type): mixed {
        return $this->tableDefaults[$type] ?? '';
    }

    protected function mapType(string $type): ?string {
        return $this->tableTypes[$type] ?? null;
    }

    protected function makeSQL(): string {
        $column  = $this->makeColumn();
        $type    = $this->makeType();
        $notNull = $this->makeNotNull();
        $unique  = $this->makeUnique();
        $default = $this->makeDefault();
        $comment = $this->makeComment();

        if (($mode = $this->getTableMode()) !== self::MODE_CREATE) {
            if ($this->table === null) {
                throw new EDatabaseError(sprintf('Table name is required for `%s` operation', $this->getTableModeName()));
            }
        }

        switch ($mode) {
        case self::MODE_CREATE:
            return "{$column} {$type} {$notNull} {$unique} {$default} {$comment}";
        case self::MODE_ADD:
            return "ALTER TABLE {$this->protectIdentifiers($this->table)} ADD COLUMN {$column} {$type} {$notNull} {$unique} {$default} {$comment}";
        case self::MODE_UPDATE:
            return "ALTER TABLE {$this->protectIdentifiers($this->table)} MODIFY COLUMN {$column} {$type} {$notNull} {$unique} {$default} {$comment}";
        case self::MODE_DROP:
            return "ALTER TABLE {$this->protectIdentifiers($this->table)} DROP COLUMN {$column}";
        default:
            throw new EDatabaseError('Unsupported mode');
        }
    }

    protected function makeConstraint(string $type) {
        if (($result = $this->constraint) === null || $result === []) {
            $result = $this->mapDefault($type);
        }

        if (is_array($result)) {
            $result = implode(',', array_slice($result, 0, 2));
        } elseif (is_float($result)) {
            $result = implode(',', array_slice(explode('.', (string) $result), 0, 2));
        }

        return $result;
    }

    protected function makeComment(): string {
        if (empty($this->comment) || is_string($this->comment) != true) {
            return '';
        }

        return "COMMENT '" . addslashes($this->comment) . "'";
    }

    protected function makeDefault(): string {
        if ($this->default === null) {
            return '';
        }

        $result = ' DEFAULT ';
        switch (gettype($this->default)) {
        case 'integer':
            $result .= (string) $this->default;
            break;
        case 'double':
        case 'float':
            $result .= str_replace(',', '.', (string) $this->default);
            break;
        case 'boolean':
            $result .= $this->default ? 'TRUE' : 'FALSE';
            break;
        default:
            $result .= $this->default;
        }

        return $result;
    }

    protected function makeColumn(): string {
        if ($this->column === null) {
            return '';
        }

        return "{$this->protectIdentifiers($this->column)}";
    }

    protected function makeNotNull(): string {
        return $this->type != static::TYPE_PK && $this->type != static::TYPE_BIGPK ? ($this->isNotNull == true ? 'NOT NULL' : 'NULL') : '';
    }

    protected function makeType(): string {
        if (($str = $this->mapType($this->type)) !== null) {
            return sprintf($str, $this->makeConstraint($this->type));
        }

        throw new EDatabaseError(sprintf('Column type "%s" is not supported', $this->type));
    }

    protected function makeUnique(): string {
        return $this->isUnique ? 'UNIQUE' : '';
    }

    /**
     * Get column size or precision definition. This is what goes into the parenthesis after
     *
     * @return  integer|string|array
     */
    public function getConstraint(): mixed {
        return $this->constraint;
    }

    /**
     * Set column size or precision definition. This is what goes into the parenthesis after
     *
     * @param  integer|string|array  $constraint  column size or precision definition. This is what goes into the parenthesis after
     *
     * @return  self
     */
    public function setConstraint(mixed $constraint): self {
        $this->constraint = $constraint;
        return $this;
    }
}
/** End of ColumnBuilder **/