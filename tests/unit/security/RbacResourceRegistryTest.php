<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Scaleum\Security\Contracts\RbacResourceInterface;
use Scaleum\Security\Permission;
use Scaleum\Security\Services\RbacResourceRegistry;

final class RegistryDocResource implements RbacResourceInterface
{
    public static function getSupportedPermissions(): array
    {
        return [Permission::READ, Permission::WRITE];
    }

    public static function getDescription(): ?string
    {
        return 'Documents';
    }

    public static function getId(): string
    {
        return 'document';
    }

    public static function getName(): string
    {
        return 'Document';
    }
}

final class RegistryReportResource implements RbacResourceInterface
{
    public static function getSupportedPermissions(): array
    {
        return [Permission::READ, Permission::EXPORT];
    }

    public static function getDescription(): ?string
    {
        return 'Reports';
    }

    public static function getId(): string
    {
        return 'report';
    }

    public static function getName(): string
    {
        return 'Report';
    }
}

final class RegistryDuplicateIdResource implements RbacResourceInterface
{
    public static function getSupportedPermissions(): array
    {
        return [];
    }

    public static function getDescription(): ?string
    {
        return null;
    }

    public static function getId(): string
    {
        return 'document';
    }

    public static function getName(): string
    {
        return 'Duplicate';
    }
}

final class RegistryDocResourceV2 implements RbacResourceInterface
{
    public static function getSupportedPermissions(): array
    {
        return [Permission::READ];
    }

    public static function getDescription(): ?string
    {
        return 'Documents v2';
    }

    public static function getId(): string
    {
        return 'document';
    }

    public static function getName(): string
    {
        return 'Document V2';
    }
}

final class RegistryEmptyIdResource implements RbacResourceInterface
{
    public static function getSupportedPermissions(): array
    {
        return [];
    }

    public static function getDescription(): ?string
    {
        return null;
    }

    public static function getId(): string
    {
        return '';
    }

    public static function getName(): string
    {
        return 'Empty';
    }
}

final class RbacResourceRegistryTest extends TestCase
{
    public function testCanRegisterDefinitionWithoutClass(): void
    {
        $registry = new RbacResourceRegistry();
        $registry->registerDefinition([
            'id' => 'invoice',
            'name' => 'Invoice',
            'description' => 'Invoice resource',
            'permissions' => [Permission::READ, Permission::WRITE],
        ]);

        $this->assertTrue($registry->has('invoice'));

        $description = $registry->describe('invoice');
        $this->assertSame('Invoice', $description['name']);
        $this->assertSame([Permission::READ, Permission::WRITE], $description['permissions']);

        $this->expectException(OutOfBoundsException::class);
        $this->expectExceptionMessage('has no bound class');
        $registry->get('invoice');
    }

    public function testRegistersResourcesAndReturnsDescription(): void
    {
        $registry = new RbacResourceRegistry();
        $registry->registerMany([RegistryDocResource::class, RegistryReportResource::class]);

        $this->assertTrue($registry->has('document'));
        $this->assertSame(RegistryDocResource::class, $registry->get('document'));
        $this->assertSame('report', $registry->getIdByClass(RegistryReportResource::class));

        $description = $registry->describe('document');

        $this->assertSame('document', $description['id']);
        $this->assertSame('Document', $description['name']);
        $this->assertSame('Documents', $description['description']);
        $this->assertSame([Permission::READ, Permission::WRITE], $description['permissions']);
    }

    public function testThrowsWhenDuplicateResourceIdIsRegistered(): void
    {
        $registry = new RbacResourceRegistry();
        $registry->register(RegistryDocResource::class);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Duplicate RBAC resource id `document`');

        $registry->register(RegistryDuplicateIdResource::class);
    }

    public function testThrowsWhenEmptyResourceIdIsRegistered(): void
    {
        $registry = new RbacResourceRegistry();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('returned empty id');

        $registry->register(RegistryEmptyIdResource::class);
    }

    public function testCompareWithDetectsOnlyInCurrentAndOutdatedInOther(): void
    {
        $current = new RbacResourceRegistry();
        $current->registerMany([RegistryDocResource::class, RegistryReportResource::class]);

        $legacy = new RbacResourceRegistry();
        $legacy->register(RegistryDocResource::class);
        $legacyOnly = new class implements RbacResourceInterface {
            public static function getSupportedPermissions(): array { return [Permission::READ]; }
            public static function getDescription(): ?string { return 'Legacy'; }
            public static function getId(): string { return 'legacy'; }
            public static function getName(): string { return 'Legacy'; }
        };

        $legacy->register($legacyOnly::class);

        $diff = $current->compareWith($legacy);

        $this->assertSame([
            [
                'id' => 'report',
                'name' => 'Report',
                'description' => 'Reports',
                'permissions' => [Permission::READ, Permission::EXPORT],
                'class' => RegistryReportResource::class,
            ],
        ], $diff['onlyInCurrent']);
        $this->assertSame([
            [
                'id' => 'legacy',
                'name' => 'Legacy',
                'description' => 'Legacy',
                'permissions' => [Permission::READ],
                'class' => $legacyOnly::class,
            ],
        ], $diff['onlyInOther']);
        $this->assertSame([
            [
                'id' => 'legacy',
                'name' => 'Legacy',
                'description' => 'Legacy',
                'permissions' => [Permission::READ],
                'class' => $legacyOnly::class,
            ],
        ], $diff['outdatedInOther']);
        $this->assertSame(['legacy'], $current->getOutdatedInOther($legacy));
    }

    public function testCompareWithWorksForDefinitionOnlyRegistries(): void
    {
        $current = new RbacResourceRegistry();
        $current->registerDefinitions([
            ['id' => 'a', 'name' => 'A', 'permissions' => [Permission::READ]],
            ['id' => 'b', 'name' => 'B', 'permissions' => [Permission::WRITE]],
        ]);

        $other = new RbacResourceRegistry();
        $other->registerDefinitions([
            ['id' => 'b', 'name' => 'B', 'permissions' => [Permission::WRITE]],
            ['id' => 'c', 'name' => 'C', 'permissions' => [Permission::DELETE]],
        ]);

        $diff = $current->compareWith($other);

        $this->assertSame([
            ['id' => 'a', 'name' => 'A', 'description' => null, 'permissions' => [Permission::READ], 'class' => null],
        ], $diff['onlyInCurrent']);
        $this->assertSame([
            ['id' => 'c', 'name' => 'C', 'description' => null, 'permissions' => [Permission::DELETE], 'class' => null],
        ], $diff['onlyInOther']);
        $this->assertSame([
            ['id' => 'c', 'name' => 'C', 'description' => null, 'permissions' => [Permission::DELETE], 'class' => null],
        ], $diff['outdatedInOther']);
        $this->assertSame([], $diff['classMismatches']);
    }

    public function testCompareWithDetectsClassMismatchForSameResourceId(): void
    {
        $current = new RbacResourceRegistry();
        $current->register(RegistryDocResource::class);

        $other = new RbacResourceRegistry();
        $other->register(RegistryDocResourceV2::class);

        $diff = $current->compareWith($other);

        $this->assertSame([
            [
                'current' => [
                    'id' => 'document',
                    'name' => 'Document',
                    'description' => 'Documents',
                    'permissions' => [Permission::READ, Permission::WRITE],
                    'class' => RegistryDocResource::class,
                ],
                'other' => [
                    'id' => 'document',
                    'name' => 'Document V2',
                    'description' => 'Documents v2',
                    'permissions' => [Permission::READ],
                    'class' => RegistryDocResourceV2::class,
                ],
            ],
        ], $diff['classMismatches']);
    }
}
