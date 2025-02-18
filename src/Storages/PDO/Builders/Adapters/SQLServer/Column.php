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

namespace Scaleum\Storages\PDO\Builders\Adapters\SQLServer;

use Scaleum\Storages\PDO\Builders\ColumnBuilder;

/**
 * Column
 *
 * @author Maxim Kirichenko <kirichenko.maxim@gmail.com>
 */
class Column extends ColumnBuilder {
    protected string $identifierQuoteLeft  = "[";
    protected string $identifierQuoteRight = "]";
    protected $tableTypes                  = [
        self::TYPE_PK          => 'int NOT NULL IDENTITY(1,1) PRIMARY KEY',
        self::TYPE_BIGPK       => 'bigint NOT NULL IDENTITY(1,1) PRIMARY KEY',
        self::TYPE_STRING      => 'nvarchar(%s)',
        self::TYPE_TEXT        => 'nvarchar(MAX)',
        self::TYPE_MEDIUM_TEXT => 'nvarchar(MAX)',
        self::TYPE_LONG_TEXT   => 'nvarchar(MAX)',
        self::TYPE_TINYINT     => 'tinyint',
        self::TYPE_SMALLINT    => 'smallint',
        self::TYPE_INTEGER     => 'int',
        self::TYPE_BIGINT      => 'bigint',
        self::TYPE_FLOAT       => 'float',
        self::TYPE_DOUBLE      => 'float',
        self::TYPE_DECIMAL     => 'decimal(%s)',
        self::TYPE_DATETIME    => 'datetime2',
        self::TYPE_TIMESTAMP   => 'datetime2',
        self::TYPE_TIME        => 'time',
        self::TYPE_DATE        => 'date',
        self::TYPE_BINARY      => 'varbinary(MAX)',
        self::TYPE_BOOLEAN     => 'bit', // В SQL Server boolean — это bit (0/1)
        self::TYPE_MONEY       => 'money',
        self::TYPE_JSON        => 'nvarchar(MAX)', // SQL Server имеет JSON, но тип отдельный не нужен
    ];
    protected $tableDefaults = [
        self::TYPE_PK          => null, // IDENTITY не требует размера
        self::TYPE_BIGPK       => null,
        self::TYPE_STRING      => 255,
        self::TYPE_TEXT        => null, // NVARCHAR(MAX) не требует размера
        self::TYPE_MEDIUM_TEXT => null,
        self::TYPE_LONG_TEXT   => null,
        self::TYPE_TINYINT     => null, // TINYINT фиксирован (1 байт)
        self::TYPE_SMALLINT    => null,
        self::TYPE_INTEGER     => null,
        self::TYPE_BIGINT      => null,
        self::TYPE_FLOAT       => null, // FLOAT не требует размеров
        self::TYPE_DOUBLE      => null,
        self::TYPE_DECIMAL     => [10, 0],
        self::TYPE_DATETIME    => null,
        self::TYPE_TIMESTAMP   => null,
        self::TYPE_TIME        => null,
        self::TYPE_DATE        => null,
        self::TYPE_BINARY      => null, // VARBINARY(MAX) не требует размера
        self::TYPE_BOOLEAN     => null, // BIT фиксированный (1 бит)
        self::TYPE_MONEY       => null, // MONEY фиксированный, не требует размера
        self::TYPE_JSON        => null, // JSON хранится как NVARCHAR(MAX)
    ];

    protected function makeLocation(): string {
        return '';
    }

    protected function makeComment(): string {
        return '';
    }

    protected function makeUnique(): string {
        return $this->isUnique ? sprintf(' CONSTRAINT UQ_%s UNIQUE (%s)', $this->protectIdentifiers($this->columnName), $this->protectIdentifiers($this->columnName)) : '';
    }
}
/** End of Column **/