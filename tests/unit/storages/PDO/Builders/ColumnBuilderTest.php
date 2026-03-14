<?php
declare (strict_types = 1);

use PHPUnit\Framework\TestCase;
use Scaleum\Storages\PDO\Builders\ColumnBuilder;
use Scaleum\Storages\PDO\Builders\Contracts\ColumnBuilderInterface;
use Scaleum\Storages\PDO\Database;

class ColumnBuilderTest extends TestCase {
    private Database $database;
    protected function setUp(): void {
        $this->database = new Database([
            'dsn'      => 'mysql:host=localhost;dbname=test',
            'user'     => 'root',
            'password' => '',
        ]);
    }

    public function testColumnPrimaryKey(): void {
        /** @var ColumnBuilderInterface $column */
        $column = ColumnBuilder::create($this->database->getPDODriverName(), [ColumnBuilder::TYPE_PK, 11, $this->database]);
        $sql    = $column
            ->setColumn('id')
            ->setNotNull()
            ->setComment('Primary key');
        fwrite(STDOUT, "\n");
        fwrite(STDOUT, str_pad('Column - Primary Key(int)', 76, '-', STR_PAD_BOTH) . "\n");
        fwrite(STDOUT, (string) $sql);
        fwrite(STDOUT, "\n");

        $this->assertEquals("`id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY COMMENT 'Primary key'", (string) $sql);
    }

    public function testColumnString(): void {
        /** @var ColumnBuilderInterface $column */
        $column = ColumnBuilder::create($this->database->getPDODriverName(), [ColumnBuilder::TYPE_STRING, 32, $this->database]);
        $sql    = $column
            ->setColumn('str')
            ->setNotNull()
            ->setDefaultValue('default', true)
            ->setComment('Some field');
        fwrite(STDOUT, "\n");
        fwrite(STDOUT, str_pad('Column - String(32)', 76, '-', STR_PAD_BOTH) . "\n");
        fwrite(STDOUT, (string) $sql);
        fwrite(STDOUT, "\n");

        $this->assertEquals("`str` varchar(32) NOT NULL DEFAULT 'default' COMMENT 'Some field'", (string)$sql);
    }

    public function testColumnText(): void {
        /** @var ColumnBuilderInterface $column */
        $column = ColumnBuilder::create($this->database->getPDODriverName(), [ColumnBuilder::TYPE_TEXT, null, $this->database]);
        $sql    = $column
            ->setColumn('text')
            ->setNotNull(false)
            ->setComment('Some text field');
        fwrite(STDOUT, "\n");
        fwrite(STDOUT, str_pad('Column - Text', 76, '-', STR_PAD_BOTH) . "\n");
        fwrite(STDOUT, (string) $sql);
        fwrite(STDOUT, "\n");

        $this->assertEquals("`text` text NULL COMMENT 'Some text field'", (string)$sql);
    }
    
    public function testColumnInt(): void {
        /** @var ColumnBuilderInterface $column */
        $column = ColumnBuilder::create($this->database->getPDODriverName(), [ColumnBuilder::TYPE_INTEGER, 11, $this->database]);
        $sql    = $column
            ->setColumn('int')
            ->setNotNull()
            ->setDefaultValue(123, false);
        fwrite(STDOUT, "\n");
        fwrite(STDOUT, str_pad('Column - Int(11)', 76, '-', STR_PAD_BOTH) . "\n");
        fwrite(STDOUT, (string) $sql);
        fwrite(STDOUT, "\n");

        $this->assertEquals("`int` int(11) NOT NULL DEFAULT 123", (string)$sql);
    }
    
    public function testColumnTinyInt(): void {
        /** @var ColumnBuilderInterface $column */
        $column = ColumnBuilder::create($this->database->getPDODriverName(), [ColumnBuilder::TYPE_TINYINT, 5, $this->database]);
        $sql    = $column
            ->setColumn('tinyint')
            ->setNotNull()
            ->setDefaultValue(12345, false);
        fwrite(STDOUT, "\n");
        fwrite(STDOUT, str_pad('Column - TinyInt(5)', 76, '-', STR_PAD_BOTH) . "\n");
        fwrite(STDOUT, (string) $sql);
        fwrite(STDOUT, "\n");

        $this->assertEquals("`tinyint` tinyint(5) NOT NULL DEFAULT 12345", (string)$sql);
    }

    public function testColumnFloat(): void {
        /** @var ColumnBuilderInterface $column */
        $column = ColumnBuilder::create($this->database->getPDODriverName(), [ColumnBuilder::TYPE_FLOAT, [10,2], $this->database]);
        $sql    = $column
            ->setColumn('float')
            ->setNotNull()
            ->setDefaultValue(123.45, false);
        fwrite(STDOUT, "\n");
        fwrite(STDOUT, str_pad('Column - Float(10,2)', 76, '-', STR_PAD_BOTH) . "\n");
        fwrite(STDOUT, (string) $sql);
        fwrite(STDOUT, "\n");

        $this->assertEquals("`float` float(10,2) NOT NULL DEFAULT 123.45", (string)$sql);
    }
    
    public function testColumnTimestamp(): void {
        /** @var ColumnBuilderInterface $column */
        $column = ColumnBuilder::create($this->database->getPDODriverName(), [ColumnBuilder::TYPE_TIMESTAMP, null, $this->database]);
        $sql    = $column
            ->setColumn('date')
            ->setNotNull()
            ->setDefaultValue('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP', false);
        fwrite(STDOUT, "\n");
        fwrite(STDOUT, str_pad('Column - Timestamp', 76, '-', STR_PAD_BOTH) . "\n");
        fwrite(STDOUT, (string) $sql);
        fwrite(STDOUT, "\n");

        $this->assertEquals("`date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP", (string)$sql);
    }     
}