<?php
declare (strict_types = 1);

use PHPUnit\Framework\TestCase;
use Scaleum\Storages\PDO\Builders\Contracts\SchemaBuilderInterface;
use Scaleum\Storages\PDO\Database;

class SchemaBuilderTest extends TestCase {
    private Database $database;

    protected string $table_1 = 'users';
    protected string $table_2 = 'users_posts';

    protected function setUp(): void {
        $file           = __DIR__ . '/test.sqlite';
        $this->database = new Database([
            // 'dsn'               => 'sqlite:' . $file,
            'dsn'               => 'mysql:host=localhost;dbname=test',
            'user'              => 'root',
            'password'          => '',

            // 'dsn'               => 'pgsql:host=localhost;dbname=test;port=5432',
            // 'user'              => 'postgres',
            // 'password'          => '12345678',

            'multiple_commands' => true,
        ]);
    }

    protected function console(string $title, string $sql): void {
        fwrite(STDOUT, "\n");
        fwrite(STDOUT, str_pad($title, 76, '-', STR_PAD_BOTH) . "\n");
        fwrite(STDOUT, (string) $sql);
        fwrite(STDOUT, "\n");
    }

    public function testCreateTable(): void {
        $schema = $this->database->getSchemaBuilder();
        $schema
            ->prepare(value: false)
            ->optimize(false)
            ->addColumn([
                $schema->columnPrimaryKey(11)->setColumn('user_id'),
                $schema->columnInt(11)->setColumn('parent_id')->setNotNull()->setDefaultValue(0, FALSE),
                $schema->columnInt(11)->setColumn('child_id')->setNotNull()->setDefaultValue(0, FALSE),
                $schema->columnString(64)->setColumn('name')->setNotNull()->setDefaultValue('"Undefined"', FALSE),
                $schema->columnSmallint(1)->setColumn('is_owner')->setNotNull()->setDefaultValue(1, FALSE),
                $schema->columnSmallint(1)->setColumn('is_active')->setNotNull()->setDefaultValue(1, FALSE),
                // $schema->columnTimestamp()->name('created')->notNull()->defaultValue('CURRENT_TIMESTAMP', FALSE),
                // $schema->columnTimestamp()->name('changed')->notNull()->defaultValue('CURRENT_TIMESTAMP', FALSE),
            ])
            ->addIndex($schema->indexUnique(['name'], 'key_name'))
            // ->addIndex($schema->index(['parent_id'])) // Not supported in: SQLite, PostgreSQL
            ->createTable($this->table_1, true);

        $this->console("Create table: {$this->table_1}", (string) $this->database->getQuery());

        $schema
            ->prepare(false)
            ->optimize(false)
            ->addColumn([
                $schema->columnPrimaryKey(11)->setColumn('user_post_id'),
                $schema->columnInt(11)->setColumn('user_id')->setNotNull()->setDefaultValue(0, FALSE),
                $schema->columnString(64)->setColumn('post')->setNotNull()->setDefaultValue('"Undefined"', FALSE),
            ])
            // ->addIndex($schema->index(['user_id'])) // Not supported in: SQLite, PostgreSQL
            // ->addIndex($schema->indexUnique(['user_id'])) // Only for PostgreSQL(foregn key)!!!
            ->createTable($this->table_2, true);

        $this->console("Create table: {$this->table_2}", (string) $this->database->getQuery());
    }

    public function testAlterTableAddIndex(): void {
        /** @var SchemaBuilderInterface $schema */
        $schema = $this->database->getSchemaBuilder();
        $schema
            ->prepare(false)
            ->optimize(false)
            ->createIndex([
                $schema->index('child_id', 'key_child_id'),
                $schema->index('parent_id', 'key_parent_id'),
            ], $this->table_1);

        $this->console('Add index', (string) $this->database->getQuery());

        $schema
            ->prepare(false)
            ->optimize(false)
            ->createIndex([
                $schema->indexForeign('user_id', 'fk_users_posts')->reference('users', 'user_id'), // Not supported in: SQLite
            ], $this->table_2);

        $this->console('Add index', (string) $this->database->getQuery());
    }

    public function testAlterTableDropIndex(): void {
        /** @var SchemaBuilderInterface $schema */
        $schema = $this->database->getSchemaBuilder();

        $schema->dropIndex('key_child_id', $this->table_1);
        $this->console('Drop index', (string) $this->database->getQuery());

        $schema->dropIndex('key_parent_id', $this->table_1);
        $this->console('Drop index', (string) $this->database->getQuery());
    }

    public function testAlterTableAddColumns(): void {
        /** @var SchemaBuilderInterface $schema */
        $schema = $this->database->getSchemaBuilder();
        $schema
            ->prepare(false)
            ->optimize(false)
            ->createColumn(
                [
                    $schema->columnString(255)->setColumn('email')->setNotNull(FALSE),
                    $schema->columnString(255)->setColumn('phone')->setNotNull(FALSE),
                ],
                $this->table_1
            );

        $this->console('Create column', (string) $this->database->getQuery());
    }

    public function testAlterTableModifyColumn(): void {
        /** @var SchemaBuilderInterface $schema */
        $schema = $this->database->getSchemaBuilder();
        $schema
            ->prepare(false)
            ->updateColumn($schema->columnString(64)->setColumn('email'), $this->table_1); // Not supported in: SQLite
        $this->console('Modify column', (string) $this->database->getQuery());
    }

    public function testAlterTableDropColumns(): void {
        /** @var SchemaBuilderInterface $schema */
        $schema = $this->database->getSchemaBuilder();
        $schema
            ->prepare(false)
            ->dropColumn(['email', $schema->columnString(255)->setColumn('phone')], $this->table_1); // Not supported in: SQLite
        $this->console('Delete columns', (string) $this->database->getQuery());
    }

    public function testTableDescribe(): void {
        /** @var SchemaBuilderInterface $schema */
        $schema = $this->database->getSchemaBuilder();
        $result = $schema->showTable($this->table_1);
        $this->console('Show table', (string) $this->database->getQuery());
        // $this->console('Table:', var_export($result, true));
    }

    public function testTableIndexes(): void {
        /** @var SchemaBuilderInterface $schema */
        $schema = $this->database->getSchemaBuilder();
        $result = $schema->showIndex($this->table_1);
        $this->console('Show table indexes', (string) $this->database->getQuery());
        // $this->console('Indexes:', var_export($result, true));
    }

    public function testShowTables(): void {
        /** @var SchemaBuilderInterface $schema */
        $schema = $this->database->getSchemaBuilder();
        $result = $schema->showTables();
        $this->console('Show database tables', (string) $this->database->getQuery());
        // $this->console('Tables:', var_export($result, true));
    }

    public function testShowDatabases(): void {
        /** @var SchemaBuilderInterface $schema */
        $schema = $this->database->getSchemaBuilder();
        $result = $schema->showDatabases();
        $this->console('Show databases', (string) $this->database->getQuery());
    }

    public function testDropTable(): void {
        /** @var SchemaBuilderInterface $schema */
        $schema = $this->database->getSchemaBuilder();
        $schema->dropTable($this->table_2);
        $this->console("Drop table: {$this->table_2}", (string) $this->database->getQuery());

        $schema->dropTable($this->table_1);
        $this->console("Drop table: {$this->table_1}", (string) $this->database->getQuery());
    }
}