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

namespace Scaleum\Storages\PDO\Builders\Adapters\MySQL;

use Scaleum\Stdlib\Exceptions\EDatabaseError;
use Scaleum\Storages\PDO\Builders\ColumnBuilder;

/**
 * Column
 *
 * @author Maxim Kirichenko <kirichenko.maxim@gmail.com>
 */
class Column extends ColumnBuilder {

    protected $tableTypes = [
        self::TYPE_PK          => 'int(%s)%s NOT NULL AUTO_INCREMENT PRIMARY KEY',
        self::TYPE_BIGPK       => 'bigint(%s)%s NOT NULL AUTO_INCREMENT PRIMARY KEY',
        self::TYPE_STRING      => 'varchar(%s)',
        self::TYPE_TEXT        => 'text',
        self::TYPE_MEDIUM_TEXT => 'mediumtext',
        self::TYPE_LONG_TEXT   => 'longtext',
        self::TYPE_TINYINT     => 'tinyint(%s)%s',
        self::TYPE_SMALLINT    => 'smallint(%s)%s',
        self::TYPE_INTEGER     => 'int(%s)%s',
        self::TYPE_BIGINT      => 'bigint(%s)%s',
        self::TYPE_FLOAT       => 'float(%s)%s',
        self::TYPE_DOUBLE      => 'double(%s)%s',
        self::TYPE_DECIMAL     => 'decimal(%s)%s',
        self::TYPE_DATETIME    => 'datetime',
        self::TYPE_TIMESTAMP   => 'timestamp',
        self::TYPE_TIME        => 'time',
        self::TYPE_DATE        => 'date',
        self::TYPE_BINARY      => 'blob',
        self::TYPE_BOOLEAN     => 'tinyint(%s)%s',
        self::TYPE_MONEY       => 'decimal(%s)%s',
        self::TYPE_JSON        => 'json',
    ];

    protected $tableDefaults = [
        self::TYPE_PK          => 11,
        self::TYPE_BIGPK       => 20,
        self::TYPE_STRING      => 255,
        self::TYPE_TEXT        => null,
        self::TYPE_MEDIUM_TEXT => null,
        self::TYPE_LONG_TEXT   => null,
        self::TYPE_TINYINT     => 3,
        self::TYPE_SMALLINT    => 6,
        self::TYPE_INTEGER     => 11,
        self::TYPE_BIGINT      => 20,
        self::TYPE_FLOAT       => [10, 0],
        self::TYPE_DOUBLE      => [10, 0],
        self::TYPE_DECIMAL     => [10, 0],
        self::TYPE_DATETIME    => null,
        self::TYPE_TIMESTAMP   => null,
        self::TYPE_TIME        => null,
        self::TYPE_DATE        => null,
        self::TYPE_BINARY      => null,
        self::TYPE_BOOLEAN     => 1,
        self::TYPE_MONEY       => [19, 4],
        self::TYPE_JSON        => null,
    ];

    protected function makeType(): string {
        if (($str = $this->mapType($this->type)) !== null) {
            return sprintf($str, $this->makeConstraint($this->type), $this->isUnsigned ? ' UNSIGNED' : '');
        }

        throw new EDatabaseError(sprintf('Column type "%s" is not supported', $this->type));
    }    
}
/** End of Column **/