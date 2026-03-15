<?php
declare(strict_types=1);

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Scaleum\Security\Permission;
use Scaleum\Security\Subject;
use Scaleum\Storages\PDO\Database;
use Scaleum\Storages\PDO\ModelAbstract;
use Scaleum\Storages\PDO\RecordsetAbstract;

final class RecordsetModelStub extends ModelAbstract
{
    public int $updateCalls = 0;
    public int $deleteCalls = 0;

    public function update(): int
    {
        $this->updateCalls++;
        return 1;
    }

    public function delete(bool $cascade = false): int
    {
        $this->deleteCalls++;
        return 1;
    }
}

final class TestRecordset extends RecordsetAbstract
{
    private string $query;

    public function __construct(
        ?Database $database = null,
        ?string $modelClass = null,
        array $records = [],
        string $query = 'SELECT id, owner_id, group_id, owner_perms, group_perms, other_perms FROM documents LIMIT :limit OFFSET :offset'
    ) {
        $this->query = $query;
        parent::__construct($database, $modelClass, $records);
    }

    protected function getQuery(): string
    {
        return $this->query;
    }
}

final class RecordsetAbstractTest extends TestCase
{
    public function testConstructorThrowsWhenModelClassDoesNotExist(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Model class `Not\\Existing\\ModelClass` does not exist');

        new TestRecordset(null, 'Not\\Existing\\ModelClass');
    }

    public function testSetRecordsHydratesModelsWhenModelClassConfigured(): void
    {
        /** @var MockObject&Database $database */
        $database = $this->getMockBuilder(Database::class)
            ->disableOriginalConstructor()
            ->getMock();

        $recordset = new TestRecordset($database, RecordsetModelStub::class, [
            ['id' => 1, 'name' => 'alpha'],
            ['id' => 2, 'name' => 'beta'],
        ]);

        $records = $recordset->getRecords();

        $this->assertCount(2, $records);
        $this->assertInstanceOf(RecordsetModelStub::class, $records[0]);
        $this->assertSame(1, $records[0]->id);
        $this->assertSame('beta', $records[1]->name);
    }

    public function testAddConvertsModelToArrayWhenModelClassNotConfigured(): void
    {
        $recordset = new TestRecordset();
        $model = (new RecordsetModelStub())->load(['id' => 7, 'name' => 'item-7']);

        $recordset->add($model);

        $records = $recordset->getRecords();
        $this->assertIsArray($records[0]);
        $this->assertSame(['id' => 7, 'name' => 'item-7'], $records[0]);
    }

    public function testRemoveMovesModelToRemovedCollection(): void
    {
        $modelOne = (new RecordsetModelStub())->load(['id' => 1, 'name' => 'a']);
        $modelTwo = (new RecordsetModelStub())->load(['id' => 2, 'name' => 'b']);

        $recordset = new TestRecordset(null, null, [$modelOne, $modelTwo]);
        $recordset->remove($modelTwo);

        $records = array_values($recordset->getRecords());

        $this->assertCount(1, $records);
        $this->assertSame(1, $records[0]->id);
        $this->assertCount(1, $recordset->getRemoved());
        $this->assertSame(2, $recordset->getRemoved()[0]->id);
    }

    public function testSaveCallsDeleteForRemovedAndUpdateForActiveModels(): void
    {
        $modelOne = (new RecordsetModelStub())->load(['id' => 1, 'name' => 'a']);
        $modelTwo = (new RecordsetModelStub())->load(['id' => 2, 'name' => 'b']);

        $recordset = new TestRecordset(null, null, [$modelOne, $modelTwo]);
        $recordset->remove($modelTwo);
        $recordset->save();

        $this->assertSame(1, $modelOne->updateCalls);
        $this->assertSame(1, $modelTwo->deleteCalls);
        $this->assertSame([], $recordset->getRemoved());
    }

    public function testLoadPopulatesRecordsAndCounts(): void
    {
        /** @var MockObject&Database $database */
        $database = $this->getMockBuilder(Database::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['setQuery', 'fetchAll', 'fetchColumn'])
            ->getMock();

        $queries = [];

        $database->expects($this->exactly(2))
            ->method('setQuery')
            ->willReturnCallback(function (string $sql, array $params = []) use (&$queries, $database) {
                $queries[] = $sql;
                return $database;
            });

        $database->expects($this->once())
            ->method('fetchAll')
            ->with([PDO::FETCH_ASSOC])
            ->willReturn([
                ['id' => 10, 'name' => 'x'],
                ['id' => 11, 'name' => 'y'],
            ]);

        $database->expects($this->once())
            ->method('fetchColumn')
            ->willReturn('15');

        $recordset = new TestRecordset($database, null, [], 'SELECT id, name FROM users ORDER BY id DESC LIMIT :limit OFFSET :offset');
        $recordset->setParams([':limit' => 2, ':offset' => 0])->load();

        $this->assertSame(2, $recordset->getRecordCount());
        $this->assertSame(15, $recordset->getRecordTotalCount());
        $this->assertCount(2, $recordset->getRecords());

        $this->assertStringContainsString('SELECT COUNT(*) AS total FROM (SELECT id, name FROM users ORDER BY id DESC) AS SUB_QUERY', $queries[1]);
    }

    public function testFilterSupportsAclAllPermissionSemantics(): void
    {
        $subject = new Subject(10, [2]);
        $required = Permission::READ | Permission::WRITE;
        /** @var MockObject&Database $database */
        $database = $this->getMockBuilder(Database::class)
            ->disableOriginalConstructor()
            ->getMock();

        $recordset = new TestRecordset($database, null, [
            ['id' => 1, 'owner_id' => 10, 'group_id' => 3, 'owner_perms' => Permission::READ | Permission::WRITE, 'group_perms' => 0, 'other_perms' => 0],
            ['id' => 2, 'owner_id' => 11, 'group_id' => 2, 'owner_perms' => 0, 'group_perms' => Permission::READ, 'other_perms' => 0],
            ['id' => 3, 'owner_id' => 12, 'group_id' => 5, 'owner_perms' => 0, 'group_perms' => 0, 'other_perms' => Permission::READ | Permission::WRITE],
        ]);

        $filtered = $recordset->filter(function (array $row) use ($subject, $required): bool {
            return $this->hasAllAclPermissions($row, $subject, $required);
        });

        $ids = array_column($filtered->toArray(), 'id');

        $this->assertSame([1, 3], $ids);
    }

    public function testFilterSupportsAclAnyPermissionSemantics(): void
    {
        $subject = new Subject(10, [2]);
        $required = Permission::READ | Permission::WRITE;
        /** @var MockObject&Database $database */
        $database = $this->getMockBuilder(Database::class)
            ->disableOriginalConstructor()
            ->getMock();

        $recordset = new TestRecordset($database, null, [
            ['id' => 1, 'owner_id' => 10, 'group_id' => 3, 'owner_perms' => Permission::READ, 'group_perms' => 0, 'other_perms' => 0],
            ['id' => 2, 'owner_id' => 11, 'group_id' => 2, 'owner_perms' => 0, 'group_perms' => Permission::WRITE, 'other_perms' => 0],
            ['id' => 3, 'owner_id' => 12, 'group_id' => 5, 'owner_perms' => 0, 'group_perms' => 0, 'other_perms' => 0],
        ]);

        $filtered = $recordset->filter(function (array $row) use ($subject, $required): bool {
            return $this->hasAnyAclPermissions($row, $subject, $required);
        });

        $ids = array_column($filtered->toArray(), 'id');

        $this->assertSame([1, 2], $ids);
    }

    private function hasAllAclPermissions(array $row, Subject $subject, int $required): bool
    {
        if ((int) ($row['owner_id'] ?? 0) === $subject->getUserId()) {
            return ((((int) ($row['owner_perms'] ?? 0)) & $required) === $required);
        }

        $groupId = (int) ($row['group_id'] ?? 0);
        if (in_array($groupId, $subject->getGroupIds(), true)) {
            return ((((int) ($row['group_perms'] ?? 0)) & $required) === $required);
        }

        return ((((int) ($row['other_perms'] ?? 0)) & $required) === $required);
    }

    private function hasAnyAclPermissions(array $row, Subject $subject, int $required): bool
    {
        if ((int) ($row['owner_id'] ?? 0) === $subject->getUserId()) {
            return ((((int) ($row['owner_perms'] ?? 0)) & $required) !== 0);
        }

        $groupId = (int) ($row['group_id'] ?? 0);
        if (in_array($groupId, $subject->getGroupIds(), true)) {
            return ((((int) ($row['group_perms'] ?? 0)) & $required) !== 0);
        }

        return ((((int) ($row['other_perms'] ?? 0)) & $required) !== 0);
    }
}
