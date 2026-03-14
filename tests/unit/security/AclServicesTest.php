<?php
declare(strict_types=1);

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Scaleum\Security\Contracts\AclResourceInterface;
use Scaleum\Security\Permission;
use Scaleum\Security\Services\AclAccessQueryApplier;
use Scaleum\Security\Services\AclAccessResolver;
use Scaleum\Security\Services\AclTableGuard;
use Scaleum\Security\Subject;
use Scaleum\Storages\PDO\Builders\Contracts\QueryBuilderInterface;
use Scaleum\Storages\PDO\Builders\Contracts\SchemaBuilderInterface;
use Scaleum\Storages\PDO\Database;
use Scaleum\Storages\PDO\ModelAbstract;

final class AclModelStub extends ModelAbstract implements AclResourceInterface
{
    private string $aclTable = 'document_acl';
    private bool $allowWhenMissing = false;
    /** @var array<string, mixed>|null */
    private ?array $aclData = null;

    public function setAclTable(string $aclTable): self
    {
        $this->aclTable = $aclTable;
        return $this;
    }

    public function setAllowWhenMissing(bool $allowWhenMissing): self
    {
        $this->allowWhenMissing = $allowWhenMissing;
        return $this;
    }

    public function setAclData(?array $aclData): self
    {
        $this->aclData = $aclData;
        return $this;
    }

    public function getAclTable(): string
    {
        return $this->aclTable;
    }

    public function getAclData(): ?array
    {
        return $this->aclData;
    }

    public function isAllowedWhenAclMissing(): bool
    {
        return $this->allowWhenMissing;
    }
}

final class AclServicesTest extends TestCase
{
    protected function tearDown(): void
    {
        $reflection = new ReflectionClass(AclTableGuard::class);
        $property = $reflection->getProperty('checked');
        $property->setAccessible(true);
        $property->setValue(null, []);
    }

    public function testAclTableGuardCachesSchemaCheck(): void
    {
        /** @var MockObject&SchemaBuilderInterface $schemaBuilder */
        $schemaBuilder = $this->createMock(SchemaBuilderInterface::class);
        $schemaBuilder
            ->expects($this->once())
            ->method('existsTable')
            ->with('document_acl')
            ->willReturn(true);

        /** @var MockObject&Database $database */
        $database = $this->getMockBuilder(Database::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getSignature', 'getSchemaBuilder'])
            ->getMock();

        $database->method('getSignature')->willReturn('db-signature');
        $database->method('getSchemaBuilder')->willReturn($schemaBuilder);

        AclTableGuard::assertTableExists($database, 'document_acl');
        AclTableGuard::assertTableExists($database, 'document_acl');

        $this->assertTrue(true);
    }

    public function testAclTableGuardThrowsWhenTableMissing(): void
    {
        /** @var MockObject&SchemaBuilderInterface $schemaBuilder */
        $schemaBuilder = $this->createMock(SchemaBuilderInterface::class);
        $schemaBuilder
            ->expects($this->once())
            ->method('existsTable')
            ->with('missing_acl')
            ->willReturn(false);

        /** @var MockObject&Database $database */
        $database = $this->getMockBuilder(Database::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getSignature', 'getSchemaBuilder'])
            ->getMock();

        $database->method('getSignature')->willReturn('db-signature-2');
        $database->method('getSchemaBuilder')->willReturn($schemaBuilder);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('ACL table `missing_acl` is not found');

        AclTableGuard::assertTableExists($database, 'missing_acl');
    }

    public function testResolverSupportsAllAndAnyPermissionModes(): void
    {
        $subject = new Subject(10, [2]);

        /** @var MockObject&QueryBuilderInterface $queryBuilder */
        $queryBuilder = $this->createMock(QueryBuilderInterface::class);
        $queryBuilder->method('select')->willReturnSelf();
        $queryBuilder->method('from')->willReturnSelf();
        $queryBuilder->method('where')->willReturnSelf();
        $queryBuilder->method('limit')->willReturnSelf();
        $queryBuilder->method('row')->willReturn([
            'owner_id' => 10,
            'group_id' => 2,
            'owner_perms' => Permission::READ | Permission::EXECUTE,
            'group_perms' => 0,
            'other_perms' => 0,
        ]);

        /** @var MockObject&SchemaBuilderInterface $schemaBuilder */
        $schemaBuilder = $this->createMock(SchemaBuilderInterface::class);
        $schemaBuilder->method('existsTable')->willReturn(true);

        /** @var MockObject&Database $database */
        $database = $this->getMockBuilder(Database::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getSignature', 'getSchemaBuilder', 'getQueryBuilder'])
            ->getMock();

        $database->method('getSignature')->willReturn('db-signature-3');
        $database->method('getSchemaBuilder')->willReturn($schemaBuilder);
        $database->method('getQueryBuilder')->willReturn($queryBuilder);

        $model = new AclModelStub();
        $model->setDatabase($database);
        $model->id = 42;

        $resolver = new AclAccessResolver();

        $this->assertFalse($resolver->isAllowed($model, $subject, Permission::READ | Permission::WRITE));
        $this->assertTrue($resolver->isAllowedAny($model, $subject, Permission::READ | Permission::WRITE));
    }

    public function testResolverUsesPreloadedAclDataWithoutDatabaseQuery(): void
    {
        $subject = new Subject(10, [2]);

        /** @var MockObject&SchemaBuilderInterface $schemaBuilder */
        $schemaBuilder = $this->createMock(SchemaBuilderInterface::class);
        $schemaBuilder->expects($this->never())->method('existsTable');

        /** @var MockObject&Database $database */
        $database = $this->getMockBuilder(Database::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getSignature', 'getSchemaBuilder', 'getQueryBuilder'])
            ->getMock();

        $database->method('getSignature')->willReturn('db-signature-4');
        $database->method('getSchemaBuilder')->willReturn($schemaBuilder);
        $database->expects($this->never())->method('getQueryBuilder');

        $model = (new AclModelStub())
            ->setAclData([
                'owner_id' => 10,
                'group_id' => 2,
                'owner_perms' => Permission::READ,
                'group_perms' => 0,
                'other_perms' => 0,
            ]);

        $model->setDatabase($database);
        $model->id = 42;

        $resolver = new AclAccessResolver();

        $this->assertTrue($resolver->isAllowed($model, $subject, Permission::READ));
    }

    public function testResolverAcceptsPreloadedAclDataWithNullGroupId(): void
    {
        $subject = new Subject(10, [2]);

        /** @var MockObject&SchemaBuilderInterface $schemaBuilder */
        $schemaBuilder = $this->createMock(SchemaBuilderInterface::class);
        $schemaBuilder->expects($this->never())->method('existsTable');

        /** @var MockObject&Database $database */
        $database = $this->getMockBuilder(Database::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getSignature', 'getSchemaBuilder', 'getQueryBuilder'])
            ->getMock();

        $database->method('getSignature')->willReturn('db-signature-6');
        $database->method('getSchemaBuilder')->willReturn($schemaBuilder);
        $database->expects($this->never())->method('getQueryBuilder');

        $model = (new AclModelStub())
            ->setAclData([
                'owner_id' => 10,
                'group_id' => null,
                'owner_perms' => Permission::READ,
                'group_perms' => 0,
                'other_perms' => 0,
            ]);

        $model->setDatabase($database);
        $model->id = 42;

        $resolver = new AclAccessResolver();

        $this->assertTrue($resolver->isAllowed($model, $subject, Permission::READ));
    }

    public function testResolverFallsBackToDatabaseWhenAclDataIsEmpty(): void
    {
        $subject = new Subject(10, [2]);

        /** @var MockObject&QueryBuilderInterface $queryBuilder */
        $queryBuilder = $this->createMock(QueryBuilderInterface::class);
        $queryBuilder->expects($this->once())->method('select')->willReturnSelf();
        $queryBuilder->method('from')->willReturnSelf();
        $queryBuilder->method('where')->willReturnSelf();
        $queryBuilder->method('limit')->willReturnSelf();
        $queryBuilder->method('row')->willReturn([
            'owner_id' => 10,
            'group_id' => 2,
            'owner_perms' => Permission::READ,
            'group_perms' => 0,
            'other_perms' => 0,
        ]);

        /** @var MockObject&SchemaBuilderInterface $schemaBuilder */
        $schemaBuilder = $this->createMock(SchemaBuilderInterface::class);
        $schemaBuilder->expects($this->once())->method('existsTable')->willReturn(true);

        /** @var MockObject&Database $database */
        $database = $this->getMockBuilder(Database::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getSignature', 'getSchemaBuilder', 'getQueryBuilder'])
            ->getMock();

        $database->method('getSignature')->willReturn('db-signature-5');
        $database->method('getSchemaBuilder')->willReturn($schemaBuilder);
        $database->method('getQueryBuilder')->willReturn($queryBuilder);

        $model = (new AclModelStub())
            ->setAclData([]);

        $model->setDatabase($database);
        $model->id = 42;

        $resolver = new AclAccessResolver();

        $this->assertTrue($resolver->isAllowed($model, $subject, Permission::READ));
    }

    public function testQueryApplierBuildsAnyConditions(): void
    {
        $subject = new Subject(10, [2]);

        /** @var MockObject&QueryBuilderInterface $query */
        $query = $this->createMock(QueryBuilderInterface::class);

        $conditions = [];

        $query->method('joinInner')->willReturnSelf();
        $query->method('whereWrap')->willReturnSelf();
        $query->method('whereWrapOr')->willReturnSelf();
        $query->method('whereWrapEnd')->willReturnSelf();
        $query->method('whereIn')->willReturnSelf();

        $query->method('where')->willReturnCallback(
            function (array|string $field, mixed $value = null, bool $quoting = true) use (&$conditions, $query) {
                if (is_string($field)) {
                    $conditions[] = $field;
                }
                return $query;
            }
        );

        $resource = $this->createMock(AclResourceInterface::class);
        $resource->method('getAclTable')->willReturn('document_acl');

        $applier = new AclAccessQueryApplier();
        $applier->applyAny($query, $resource, 'd.id', $subject, Permission::READ | Permission::WRITE);

        $this->assertContains('(acl.owner_perms & 3) != 0', $conditions);
        $this->assertContains('(acl.group_perms & 3) != 0', $conditions);
        $this->assertContains('(acl.other_perms & 3) != 0', $conditions);
    }
}
