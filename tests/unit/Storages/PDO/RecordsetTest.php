<?php
declare (strict_types = 1);

use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Scaleum\Storages\PDO\Database;
use Scaleum\Storages\PDO\ModelAbstract;
use Scaleum\Storages\PDO\RecordsetAbstract;
use Scaleum\Services\ServiceManager;
use Scaleum\Stdlib\Base\Benchmark;
use Scaleum\Logger\LoggerGateway;
use Scaleum\Logger\LoggerManager;
use Scaleum\Services\ServiceLocator;

class RecordsetTest extends TestCase {
    public const TABLE = 'recordset_users';

    private Database $database;

    protected function printLine(string $line) {
        fwrite(STDOUT, $line . "\n");
    }

    protected function setUp(): void {
        ServiceLocator::strictModeOff();
        // ServiceLocator::setProvider(new ServiceManager());
        // ServiceLocator::set('benchmark', new Benchmark());
    
        LoggerGateway::strictModeOff();
        // LoggerGateway::setProvider(new LoggerManager());
        // LoggerGateway::setLogger('kernel', new LogTerminal());

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
            'logging'           => true,
        ]);

        $schema = $this->database->getSchemaBuilder();
        $schema
            ->prepare(value: false)
            ->optimize(false)
            ->addColumn([
                $schema->columnPrimaryKey(11)->setColumn('id'),
                $schema->columnString(255)->setColumn('username')->setNotNull(),
                $schema->columnString(255)->setColumn('email')->setNotNull(),
                $schema->columnTimestamp()->setColumn('created_at')->setNotNull()->setDefaultValue('CURRENT_TIMESTAMP', FALSE),
            ])
            ->createTable(self::TABLE, true);
    }

    public function testAddRecords(): void {
        $recordset = new Recordset($this->database);
        for ($i = 1; $i <= 100; $i++) {
            $recordset->add((new RecordsetUserModel($this->database))->load([
                'username' => 'user' . $i,
                'email'    => 'user' . $i . '@example.com',
            ]));
        }
        $recordset->save();
    }

    public function testGetRecords(): void {
        $recordset = new Recordset($this->database);
        $recordset->setParams(['limit' => 10, 'offset' => 0]);
        $recordset->load();

        $this->printLine("\n");
        $this->printLine('Record count: ' . $recordset->getRecordCount());
        $this->printLine('Record total count: ' . $recordset->getRecordTotalCount());
        // $this->assertEquals(10, $recordset->getRecordCount());
        // $this->assertEquals(100, $recordset->getRecordTotalCount());

        $recordset->removeByIndex(1);
        $recordset->removeByIndex(2);
        $recordset->save();        

        $recordset->setParams(['limit' => 50, 'offset' => 0]);
        $recordset->load();
        $this->printLine("\n");
        $this->printLine('Record count: ' . $recordset->getRecordCount());
        $this->printLine('Record total count: ' . $recordset->getRecordTotalCount());
        // $this->assertEquals(50, $recordset->getRecordCount());
        // $this->assertEquals(98, $recordset->getRecordTotalCount());        
    }
    
    public function testGetRecordsByPage(){
        $recordset = new Recordset($this->database);
        $limit = 10;        
        
        for ($i = 1; $i <= 10; $i++) {
            $page = $i;
            $recordset->setParams(['limit' => $limit, 'offset' => ($page - 1) * $limit]);
            $recordset->load();
            $this->printLine("\n");
            $this->printLine('Page: ' . $page);
            $this->printLine('Record count: ' . $recordset->getRecordCount());
            $this->printLine('Record total count: ' . $recordset->getRecordTotalCount());
            // $this->assertEquals($limit, $recordset->getRecordCount());
        }
    }

    public function testRemoveRecords(): void {
        $this->database->setQuery('DELETE FROM ' . self::TABLE)->execute();
    }
}

class Recordset extends RecordsetAbstract {
    public function __construct(Database $database) {
        parent::__construct($database, RecordsetUserModel::class);
    }

    protected function getQuery(): string {
        return 'SELECT * FROM ' . RecordsetTest::TABLE . ' LIMIT :limit OFFSET :offset';
    }
}

class RecordsetUserModel extends ModelAbstract {
    protected ?string $table     = RecordsetTest::TABLE;
    protected string $primaryKey = 'id';
}

class LogTerminal implements LoggerInterface{
    /**
         * System is unusable.
         *
         * @param mixed[] $context
         */
        public function emergency(string|\Stringable $message, array $context = []): void{}
    
        /**
         * Action must be taken immediately.
         *
         * Example: Entire website down, database unavailable, etc. This should
         * trigger the SMS alerts and wake you up.
         *
         * @param mixed[] $context
         */
        public function alert(string|\Stringable $message, array $context = []): void{}
    
        /**
         * Critical conditions.
         *
         * Example: Application component unavailable, unexpected exception.
         *
         * @param mixed[] $context
         */
        public function critical(string|\Stringable $message, array $context = []): void{}
    
        /**
         * Runtime errors that do not require immediate action but should typically
         * be logged and monitored.
         *
         * @param mixed[] $context
         */
        public function error(string|\Stringable $message, array $context = []): void{}
    
        /**
         * Exceptional occurrences that are not errors.
         *
         * Example: Use of deprecated APIs, poor use of an API, undesirable things
         * that are not necessarily wrong.
         *
         * @param mixed[] $context
         */
        public function warning(string|\Stringable $message, array $context = []): void{}
    
        /**
         * Normal but significant events.
         *
         * @param mixed[] $context
         */
        public function notice(string|\Stringable $message, array $context = []): void{}
    
        /**
         * Interesting events.
         *
         * Example: User logs in, SQL logs.
         *
         * @param mixed[] $context
         */
        public function info(string|\Stringable $message, array $context = []): void{}
    
        /**
         * Detailed debug information.
         *
         * @param mixed[] $context
         */
        public function debug(string|\Stringable $message, array $context = []): void{
            $this->log(0, $message, $context);
        }
    
        /**
         * Logs with an arbitrary level.
         *
         * @param mixed $level
         * @param mixed[] $context
         *
         * @throws \Psr\Log\InvalidArgumentException
         */
        public function log($level, string|\Stringable $message, array $context = []): void{
            fwrite(STDOUT, $message . "\n");
        }    
    }