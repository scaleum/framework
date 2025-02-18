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

namespace Scaleum\Storages\PDO\Builders\Adapters\PostgreSQL;

use Scaleum\Storages\PDO\Builders\ColumnBuilder;

/**
 * Column
 *
 * @author Maxim Kirichenko <kirichenko.maxim@gmail.com>
 */
class Column extends ColumnBuilder {
    protected string $identifierQuoteLeft  = '"';
    protected string $identifierQuoteRight = '"';
    protected $tableTypes                  = [
        self::TYPE_PK          => 'serial PRIMARY KEY',
        self::TYPE_BIGPK       => 'bigserial PRIMARY KEY',
        self::TYPE_STRING      => 'varchar(%s)',
        self::TYPE_TEXT        => 'text',
        self::TYPE_MEDIUM_TEXT => 'text', // В PostgreSQL нет mediumtext, longtext — только text
        self::TYPE_LONG_TEXT   => 'text',
        self::TYPE_TINYINT     => 'smallint',
        self::TYPE_SMALLINT    => 'smallint',
        self::TYPE_INTEGER     => 'integer',
        self::TYPE_BIGINT      => 'bigint',
        self::TYPE_FLOAT       => 'real',
        self::TYPE_DOUBLE      => 'double precision',
        self::TYPE_DECIMAL     => 'numeric(%s)',
        self::TYPE_DATETIME    => 'timestamp',
        self::TYPE_TIMESTAMP   => 'timestamp',
        self::TYPE_TIME        => 'time',
        self::TYPE_DATE        => 'date',
        self::TYPE_BINARY      => 'bytea',
        self::TYPE_BOOLEAN     => 'boolean',
        self::TYPE_MONEY       => 'numeric(%s)', // PostgreSQL не имеет MONEY, но numeric подходит
        self::TYPE_JSON        => 'jsonb',       // В PostgreSQL лучше использовать jsonb вместо json
    ];

    protected $tableDefaults = [
        self::TYPE_PK          => null, // SERIAL не требует длины
        self::TYPE_BIGPK       => null, // BIGSERIAL тоже
        self::TYPE_STRING      => 255,
        self::TYPE_TEXT        => null,
        self::TYPE_MEDIUM_TEXT => null,
        self::TYPE_LONG_TEXT   => null,
        self::TYPE_TINYINT     => null, // В PostgreSQL нет tinyint, smallint = 16 бит
        self::TYPE_SMALLINT    => null,
        self::TYPE_INTEGER     => null,
        self::TYPE_BIGINT      => null,
        self::TYPE_FLOAT       => null, // REAL и DOUBLE PRECISION не требуют размеров
        self::TYPE_DOUBLE      => null,
        self::TYPE_DECIMAL     => [10, 0],
        self::TYPE_DATETIME    => null,
        self::TYPE_TIMESTAMP   => null,
        self::TYPE_TIME        => null,
        self::TYPE_DATE        => null,
        self::TYPE_BINARY      => null,    // BYTEA не требует размера
        self::TYPE_BOOLEAN     => null,    // BOOLEAN не требует длины
        self::TYPE_MONEY       => [19, 4], // MONEY можно хранить в NUMERIC
        self::TYPE_JSON        => null,    // JSONB не требует размера
    ];

    protected function makeLocation(): string {
        return '';
    }

    protected function makeComment(): string {
        return '';
    }

    protected function makeDefault(): string {
        if ($this->default === null) {
            return '';
        }

        $result = ' DEFAULT ';
        switch (gettype($this->default)) {
        case 'string':
            $result .= "'{$this->default}'::text";
            break;
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
}
/** End of Column **/