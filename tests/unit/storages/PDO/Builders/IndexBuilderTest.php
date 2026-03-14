<?php
declare (strict_types = 1);

use PHPUnit\Framework\TestCase;
use Scaleum\Storages\PDO\Builders\Contracts\IndexBuilderInterface;
use Scaleum\Storages\PDO\Builders\IndexBuilder;
use Scaleum\Storages\PDO\Database;

class IndexBuilderTest extends TestCase {

    private Database $database;

    protected function getBuilder(array $args): IndexBuilderInterface {
        $result = IndexBuilder::create($this->database->getPDODriverName(), $args);
        $result->setDatabase($this->database);
        return $result;
    }

    protected function console(string $title, string $sql): void {
        fwrite(STDOUT, "\n");
        fwrite(STDOUT, str_pad($title, 76, '-', STR_PAD_BOTH) . "\n");
        fwrite(STDOUT, (string) $sql);
        fwrite(STDOUT, "\n");
    }

    protected function setUp(): void {
        $this->database = new Database([
            'dsn'      => 'mysql:host=localhost;dbname=test',
            'user'     => 'root',
            'password' => '',
        ]);
    }

    public function testPrimary() {
        /** @var IndexBuilderInterface $index */
        $index = $this->getBuilder([IndexBuilder::TYPE_PK, null, null]);
        $sql   = $index
            ->name('key_id')
            ->column('id');

        $this->console('Index - Primary Key', (string)$sql);
        // $this->assertEquals("PRIMARY KEY (`id`)", (string) $sql);
    }
    public function testPrimaryComposite() {
        /** @var IndexBuilderInterface $index */
        $index = $this->getBuilder([IndexBuilder::TYPE_PK, null, null]);
        $sql   = $index
            ->name('pk_id')
            ->column(['table.id', 'name']);
        $this->console('Index - Primary Key(composite)', (string)$sql);
        // $this->assertEquals("PRIMARY KEY (`table`.`id`,`name`)", (string) $sql);
    }
    public function testPrimaryCreate() {
        /** @var IndexBuilderInterface $index */
        $index = $this->getBuilder([IndexBuilder::TYPE_PK, null, null,'tableA']);
        $sql   = $index
            ->name('pk_id')
            ->column(['table.id', 'name']);
        $this->console('Index - Primary Key(create)', (string)$sql);
        // $this->assertEquals("PRIMARY KEY (`table`.`id`,`name`)", (string) $sql);
    }

    public function testKey() {
        /** @var IndexBuilderInterface $index */
        $index = $this->getBuilder([IndexBuilder::TYPE_INDEX, null, null]);
        $sql   = $index
            ->name('key_id')
            ->column(['field']);
        $this->console('Index - Key', (string)$sql);
        // $this->assertEquals("KEY `key_id` (`field`)", (string) $sql);
    }

    public function testKeyComposite() {
        /** @var IndexBuilderInterface $index */
        $index = $this->getBuilder([IndexBuilder::TYPE_INDEX, null, null]);
        $sql   = $index
            ->name('key_id')
            ->column(['field1', 'field2']);
        $this->console('Index - Key(composite)', (string)$sql);
        // $this->assertEquals("KEY `key_id` (`field1`,`field2`)", (string) $sql);
    }

    public function testKeyCreate() {
        /** @var IndexBuilderInterface $index */
        $index = $this->getBuilder([IndexBuilder::TYPE_INDEX, null, null,'tableA']);
        $sql   = $index
            ->name('key_id')
            ->column(['field1', 'field2']);
        $this->console('Index - Key(create)', (string)$sql);
        // $this->assertEquals("KEY `key_id` (`field1`,`field2`)", (string) $sql);
    }

    public function testUnique() {
        /** @var IndexBuilderInterface $index */
        $index = $this->getBuilder([IndexBuilder::TYPE_UNIQUE]);
        $sql   = $index
            ->name('key_id')
            ->column(['field']);

        $this->console('Index - Unique', (string)$sql);
        // $this->assertEquals("CONSTRAINT `key_id` UNIQUE (`field`)", (string) $sql);
    }

    public function testUniqueComposite() {
        /** @var IndexBuilderInterface $index */
        $index = $this->getBuilder([IndexBuilder::TYPE_UNIQUE]);
        $sql   = $index
            ->name('key_id')
            ->column(['field1', 'field2']);
        $this->console('Index - Unique(composite)', (string)$sql);
        // $this->assertEquals("CONSTRAINT `key_id` UNIQUE (`field1`,`field2`)", (string) $sql);
    }

    public function testUniqueCreate() {
        /** @var IndexBuilderInterface $index */
        $index = $this->getBuilder([IndexBuilder::TYPE_UNIQUE,null,null,'tableA']);
        $sql   = $index
            ->name('key_id')
            ->column(['field1', 'field2']);
        $this->console('Index - Unique(create)', (string)$sql);
        // $this->assertEquals("CONSTRAINT `key_id` UNIQUE (`field1`,`field2`)", (string) $sql);
    }    

    public function testFulltext() {
        /** @var IndexBuilderInterface $index */
        $index = $this->getBuilder([IndexBuilder::TYPE_FULLTEXT]);
        $sql   = $index
            ->name('key_text')
            ->column(['field']);
        $this->console('Index - Fulltext', (string)$sql);
        // $this->assertEquals("FULLTEXT `key_text` (`field`)", (string) $sql);
    }

    public function testFulltextCreate() {
        /** @var IndexBuilderInterface $index */
        $index = $this->getBuilder([IndexBuilder::TYPE_FULLTEXT,null,null,'tableA']);
        $sql   = $index
            ->name('key_text')
            ->column(['field']);
        $this->console('Index - Fulltext(create)', (string)$sql);
        // $this->assertEquals("FULLTEXT `key_text` (`field`)", (string) $sql);
    }

    public function testForeign() {
        /** @var IndexBuilderInterface $index */
        $index = $this->getBuilder([IndexBuilder::TYPE_FOREIGN]);
        $sql   = $index
            ->name('fk_key')
            ->column(['field_id'])
            ->reference('table', ['field_id']);
        $this->console('Index - Foreign', (string)$sql);
        // $this->assertEquals("CONSTRAINT `fk_key` FOREIGN KEY (`field_id`) REFERENCES `table`(`field_id`) ON DELETE CASCADE ON UPDATE CASCADE", (string) $sql);
    }

    public function testForeignCreate() {
        /** @var IndexBuilderInterface $index */
        $index = $this->getBuilder([IndexBuilder::TYPE_FOREIGN,null,null,'tableA']);
        $sql   = $index
            ->name('fk_key')
            ->column(['field_id'])
            ->reference('table', ['field_id'],'cascade','cascade');
        $this->console('Index - Foreign', (string)$sql);
        // $this->assertEquals("CONSTRAINT `fk_key` FOREIGN KEY (`field_id`) REFERENCES `table`(`field_id`) ON DELETE CASCADE ON UPDATE CASCADE", (string) $sql);
    }
}