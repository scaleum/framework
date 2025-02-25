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

namespace Scaleum\Storages\PDO\Builders\Adapters\SQLite;

use Scaleum\Stdlib\Exceptions\EDatabaseError;
use Scaleum\Storages\PDO\Builders\ColumnBuilder;

/**
 * Column
 *
 * @author Maxim Kirichenko <kirichenko.maxim@gmail.com>
 */
class Column extends ColumnBuilder {
    protected string $identifierQuoteLeft  = '"';
    protected string $identifierQuoteRight = '"';
    protected array $tableTypes            = [
        self::TYPE_PK          => 'integer PRIMARY KEY AUTOINCREMENT',
        self::TYPE_BIGPK       => 'integer PRIMARY KEY AUTOINCREMENT',
        self::TYPE_STRING      => 'text',
        self::TYPE_TEXT        => 'text',
        self::TYPE_MEDIUM_TEXT => 'text',
        self::TYPE_LONG_TEXT   => 'text',
        self::TYPE_TINYINT     => 'integer',
        self::TYPE_SMALLINT    => 'integer',
        self::TYPE_INTEGER     => 'integer',
        self::TYPE_BIGINT      => 'integer',
        self::TYPE_FLOAT       => 'real',
        self::TYPE_DOUBLE      => 'real',
        self::TYPE_DECIMAL     => 'numeric(%s)',
        self::TYPE_DATETIME    => 'text', // SQLite хранит даты как текст
        self::TYPE_TIMESTAMP   => 'text',
        self::TYPE_TIME        => 'text',
        self::TYPE_DATE        => 'text',
        self::TYPE_BINARY      => 'blob',
        self::TYPE_BOOLEAN     => 'integer', // В SQLite нет BOOLEAN, но 0 и 1 работают
        self::TYPE_MONEY       => 'numeric(%s)',
        self::TYPE_JSON        => 'text', // SQLite не имеет JSON-типа, хранится как text
    ];

    protected array $tableDefaults = [
        self::TYPE_PK          => null, // AUTOINCREMENT не требует размера
        self::TYPE_BIGPK       => null,
        self::TYPE_STRING      => 255, // SQLite не имеет VARCHAR(N), но можно указать
        self::TYPE_TEXT        => null,
        self::TYPE_MEDIUM_TEXT => null,
        self::TYPE_LONG_TEXT   => null,
        self::TYPE_TINYINT     => null, // В SQLite TINYINT = INTEGER
        self::TYPE_SMALLINT    => null, // SMALLINT = INTEGER
        self::TYPE_INTEGER     => null,
        self::TYPE_BIGINT      => null,
        self::TYPE_FLOAT       => null, // REAL не требует размеров
        self::TYPE_DOUBLE      => null,
        self::TYPE_DECIMAL     => [10, 0],
        self::TYPE_DATETIME    => null,
        self::TYPE_TIMESTAMP   => null,
        self::TYPE_TIME        => null,
        self::TYPE_DATE        => null,
        self::TYPE_BINARY      => null, // BLOB не требует размера
        self::TYPE_BOOLEAN     => null, // BOOLEAN = INTEGER (0/1)
        self::TYPE_MONEY       => [19, 4],
        self::TYPE_JSON        => null, // JSON хранится как TEXT
    ];

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
        default:
            throw new EDatabaseError("SQLite does not support '{$this->getTableModeName()}' operation");
        }
    }

    protected function makeLocation(): string {
        return '';
    }

    protected function makeComment(): string {
        return '';
    }
}
/** End of Column **/