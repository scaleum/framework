<?php
declare(strict_types=1);

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Scaleum\Storages\PDO\Builders\Adapters\MySQL\Query as MySQLQueryBuilder;
use Scaleum\Storages\PDO\Database;

final class QueryBuilderWrapsTest extends TestCase
{
    public function testNestedWhereWrapsBuildValidPrefixesWithoutAcl(): void
    {
        /** @var MockObject&Database $database */
        $database = $this->getMockBuilder(Database::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getPDO'])
            ->getMock();

        $database->method('getPDO')->willReturn(new \PDO('sqlite::memory:'));

        $query = new MySQLQueryBuilder($database);
        $query->prepare(true)
            ->select('*')
            ->from('movies AS m')
            ->where('2 = 2', null, false)
            ->whereWrap()
                ->whereWrap()
                    ->where('m.is_public = 1', null, false)
                    ->where('m.deleted_at IS NULL', null, false)
                ->whereWrapEnd()
                ->whereWrapOr()
                    ->where('m.rating >= 8', null, false)
                ->whereWrapEnd()
            ->whereWrapEnd()
            ->where('1 = 1', null, false);

        $sql = $query->rows();

        $this->assertIsString($sql);
        $this->assertStringContainsString('WHERE 2 = 2', $sql);
        $this->assertStringContainsString('AND ((', $sql);
        $this->assertStringContainsString(') OR (', $sql);
        $this->assertStringNotContainsString('(AND (', $sql);
        $this->assertStringContainsString('AND 1 = 1', $sql);
    }
}
