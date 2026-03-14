<?php
declare (strict_types = 1);

use \PDO;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Scaleum\Cache\Cache;
use Scaleum\Storages\PDO\Builders\QueryBuilder;
use Scaleum\Storages\PDO\Database;

class DatabaseTest extends TestCase {
    private Database $database;
    /** @var MockObject&PDO */
    private PDO $pdoMock;
    /** @var MockObject&Cache */
    private Cache $cacheMock;

    protected function setUp(): void {
        // $this->pdoMock   = $this->createMock(PDO::class);
        $this->pdoMock = $this->getMockBuilder(PDO::class)
                              ->disableOriginalConstructor()
                              ->onlyMethods(['beginTransaction', 'commit', 'rollBack', 'getAttribute', 'lastInsertId'])
                              ->getMock();        
        $this->cacheMock = $this->createMock(Cache::class);

        $this->database = new Database([
            'dsn'      => 'mysql:host=localhost;dbname=test',
            'user'     => 'root',
            'password' => '',
        ]);
        $this->database->setPDO($this->pdoMock);
        $this->database->setCache($this->cacheMock);
    }

    public function testBeginTransaction(): void {
        $this->pdoMock
            ->method('beginTransaction')
            ->willReturn(true);

        $this->assertTrue($this->database->begin());
    }

    public function testCommitTransaction(): void {
        $this->pdoMock
            ->method('commit')
            ->willReturn(true);

        $this->assertTrue($this->database->begin());
        $this->assertTrue($this->database->commit());
    }

    public function testRollbackTransaction(): void {
        $this->pdoMock
            ->method('rollBack')
            ->willReturn(true);
        $this->assertTrue($this->database->begin());
        $this->assertTrue($this->database->rollback());
    }

    public function testGetPDODriverName(): void {
        $this->pdoMock
            ->method('getAttribute')
            ->with(PDO::ATTR_DRIVER_NAME)
            ->willReturn('mysql');

        $this->assertEquals('mysql', $this->database->getPDODriverName());
    }

    public function testGetSignature(): void {
        $reflection  = new \ReflectionClass($this->database);
        $dsnProperty = $reflection->getProperty('dsn');
        $dsnProperty->setAccessible(true);
        $dsnProperty->setValue($this->database, 'mysql:host=localhost;dbname=test');

        $optionsProperty = $reflection->getProperty('options');
        $optionsProperty->setAccessible(true);
        $optionsProperty->setValue($this->database, []);

        $this->assertEquals(md5('mysql:host=localhost;dbname=test' . serialize([])), $this->database->getSignature());
    }

    public function testGetLastInsertID(): void {
        $this->pdoMock
            ->method('lastInsertId')
            ->with(null)
            ->willReturn('0');

        $this->assertEquals('0', $this->database->getLastInsertID());
    }

    public function testGetCache(): void {
        $this->assertInstanceOf(Cache::class, $this->database->getCache());
    }

    public function testGetCacheKey(): void {
        $this->assertEquals(md5('test'), $this->database->getCacheKey('test'));
        $this->assertEquals(md5(serialize(['test'])), $this->database->getCacheKey(['test']));
    }

    public function testSetCache(): void {
        $cache = new Cache();
        $this->database->setCache($cache);
        $this->assertSame($cache, $this->database->getCache());
    }

    public function testIsCacheable(): void {
        $this->assertTrue($this->database->isCacheable('SELECT * FROM users'));
        $this->assertFalse($this->database->isCacheable('INSERT INTO users (name) VALUES ("John")'));
    }

    public function testSetSQL(): void {
        $sql    = 'SELECT * FROM users WHERE id = :id';
        $params = [':id' => 1];
        $this->database->setQuery($sql, $params);

        $reflection       = new \ReflectionClass($this->database);
        $queryStrProperty = $reflection->getProperty('queryStr');
        $queryStrProperty->setAccessible(true);
        $this->assertEquals($sql, $queryStrProperty->getValue($this->database));

        $queryParamsProperty = $reflection->getProperty('queryParams');
        $queryParamsProperty->setAccessible(true);
        $this->assertEquals($params, $queryParamsProperty->getValue($this->database));
    }

    public function testGetSQL(): void {
        $sql    = 'SELECT * FROM users WHERE id = :id';
        $params = [':id' => 1];
        $this->database->setQuery($sql, $params);

        $this->assertEquals('SELECT * FROM users WHERE id = 1', $this->database->getQuery());
    }

    public function testCreateTable(): void {
        $sql    = 'CREATE TABLE IF NOT EXISTS users (id INT AUTO_INCREMENT PRIMARY KEY, name VARCHAR(255))';
        $this->database->setQuery($sql, []);
        $this->database->execute();
    }

    public function testGetQueryBuilder(): void {
        $this->assertInstanceOf(QueryBuilder::class, $this->database->getQueryBuilder());
    }
}