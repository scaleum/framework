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
use Scaleum\Storages\PDO\Database;

/**
 * ColumnBuilder
 *
 * @author Maxim Kirichenko <kirichenko.maxim@gmail.com>
 */
class ColumnBuilder extends BuilderAbstract implements Contracts\ColumnBuilderInterface {
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
    public const LOCATE_FIRST     = 1;
    public const LOCATE_AFTER     = 2;

    protected array $tableTypes    = [];
    protected array $tableDefaults = [];

    protected ?string $columnName = null;

    protected ?string $columnPrev = null;

    protected ?string $comment = null;

    /**
     * @var integer|string|array column size or precision definition. This is what goes into the parenthesis after
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
     * @var int
     */
    protected int $location = 0;
    /**
     * @var string the column type definition such as INTEGER, VARCHAR, DATETIME, etc.
     */
    protected string $type = 'string';

    protected static array $adapters = [
        'mysql'  => Adapters\MySQL\Column::class,
        'pgsql'  => Adapters\PostgreSQL\Column::class,
        'sqlite' => Adapters\SQLite\Column::class,
        'sqlsrv' => Adapters\SQLServer\Column::class,
        'oci'    => Adapters\Oracle\Column::class,
    ];

    public function __construct(string $type = self::TYPE_KEY, mixed $constrain = null, ?Database $database = null) {
        parent::__construct($database);

        // check if type is supported
        if (! $this->mapType($type)) {
            throw new EDatabaseError(sprintf('Column type `%s` is not supported', strtoupper($type)));
        }
        $this->type       = $type;
        $this->constraint = $constrain;
    }

    /**
     * @param string $column
     *
     * @return $this
     */
    public function after(string $column): self {
        $this->location   = self::LOCATE_AFTER;
        $this->columnPrev = $column;

        return $this;
    }

    /**
     * @param string $str
     *
     * @return $this
     */
    public function comment(string $str): self {
        $this->comment = $str;

        return $this;
    }

    public function defaultValue(mixed $default, bool $quoted = true): self {
        if (is_string($default)) {
            $default = str_replace("'", "\\'", $default);
        }
        $this->default = $quoted == true ? "'$default'" : $default;

        return $this;
    }

    public function first(): self {
        $this->location   = self::LOCATE_FIRST;
        $this->columnPrev = null;

        return $this;
    }

    public function name(string $str): self {
        $this->columnName = $str;

        return $this;
    }

    public function notNull(bool $val = true): self {
        $this->isNotNull = $val;

        return $this;
    }

    public function unique(bool $val = true): self {
        $this->isUnique = $val;

        return $this;
    }

    public function unsigned(bool $val = true): self {
        $this->isUnsigned = $val;

        return $this;
    }

    protected function mapDefault(string $type): mixed {
        return $this->tableDefaults[$type] ?? '';
    }

    protected function mapType(string $type): ?string {
        return $this->tableTypes[$type] ?? null;
    }

    protected function makeString(): string {
        return
        $this->makeName() .
        $this->makeType() .
        $this->makeNotNull() .
        $this->makeUnique() .
        $this->makeDefault() .
        $this->makeComment() .
        $this->makeLocation();
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

        return " COMMENT '" . addslashes($this->comment) . "'";
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

    protected function makeLocation(): string {
        $result = '';
        if ($this->location === static::LOCATE_FIRST) {
            $result = ' FIRST';
        } elseif ($this->location === static::LOCATE_AFTER && ! empty($this->columnPrev)) {
            $result = ' AFTER ' . $this->protectIdentifiers($this->columnPrev);
        }

        return $result;
    }

    protected function makeName(): string {
        if ($this->columnName === null) {
            return '';
        }

        return "{$this->columnName} ";
    }

    protected function makeNotNull(): string {
        return $this->isNotNull == true ? ' NOT NULL' : ($this->type != static::TYPE_PK && $this->type != static::TYPE_BIGPK ? ' NULL' : '');
    }

    protected function makeType(): string {
        if (($str = $this->mapType($this->type)) !== null) {
            return sprintf($str, $this->makeConstraint($this->type));
        }

        throw new EDatabaseError(sprintf('Column type "%s" is not supported', $this->type));
    }

    protected function makeUnique(): string {
        return $this->isUnique ? ' UNIQUE' : '';
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
    public function setConstraint(mixed $constraint = null): self {
        $this->constraint = $constraint;

        return $this;
    }

    public function getColumnName(): string {
        return $this->columnName;
    }

    public function setColumnName(string $name): self {
        $this->columnName = $name;

        return $this;
    }

    public function getColumnPrev(): mixed {
        return $this->columnPrev;
    }

    public function setColumnPrev(string $name): self {
        $this->columnPrev = $name;

        return $this;
    }
}
/** End of ColumnBuilder **/