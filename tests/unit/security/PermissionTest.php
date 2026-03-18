<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Scaleum\Security\Permission;
use Scaleum\Stdlib\Exceptions\EInvalidArgumentException;
use Scaleum\Stdlib\Exceptions\ERuntimeError;

final class TestAppPermission extends Permission
{
    public const APPROVE = 1 << 8;

    protected static array $perms = Permission::BASE_LABELS + [
        self::APPROVE => 'Approve',
    ];
}

final class TestWidePermission extends Permission
{
    public const ARCHIVE = 1 << 40;

    protected static array $perms = Permission::BASE_LABELS + [
        self::ARCHIVE => 'Archive',
    ];
}

final class PermissionTest extends TestCase
{
    protected function tearDown(): void
    {
        Permission::setMaxBits(Permission::DEFAULT_MAX_BITS);
    }

    public function testHasChecksAllBits(): void
    {
        $mask = Permission::READ | Permission::WRITE;

        $this->assertTrue(Permission::has($mask, Permission::READ));
        $this->assertTrue(Permission::has($mask, Permission::READ | Permission::WRITE));
        $this->assertFalse(Permission::has($mask, Permission::READ | Permission::DELETE));
    }

    public function testHasAnyChecksAtLeastOneBit(): void
    {
        $mask = Permission::READ;

        $this->assertTrue(Permission::hasAny($mask, Permission::READ | Permission::WRITE));
        $this->assertFalse(Permission::hasAny($mask, Permission::WRITE | Permission::DELETE));
    }

    public function testNoneIsCheckedExplicitlyInHas(): void
    {
        $this->assertTrue(Permission::has(Permission::NONE, Permission::NONE));
        $this->assertFalse(Permission::has(Permission::READ, Permission::NONE));
    }

    public function testLabelAndLabelsReturnHumanReadableNames(): void
    {
        $this->assertSame('Read', Permission::label(Permission::READ));
        $this->assertNull(Permission::label(1 << 20));

        $labels = Permission::labels(Permission::READ | Permission::WRITE);

        $this->assertSame([
            Permission::READ => 'Read',
            Permission::WRITE => 'Write',
        ], $labels);
    }

    public function testSubclassLabelsUseLateStaticBinding(): void
    {
        $labels = TestAppPermission::labels(TestAppPermission::APPROVE | Permission::READ);

        $this->assertSame([
            Permission::READ => 'Read',
            TestAppPermission::APPROVE => 'Approve',
        ], $labels);
        $this->assertSame('Approve', TestAppPermission::label(TestAppPermission::APPROVE));
    }

    public function testAllBuildsMaskFromSubclassRegistry(): void
    {
        // Base mask does not include project-specific bits from subclasses.
        $this->assertFalse(Permission::has(Permission::BASE_ALL, TestAppPermission::APPROVE));
        $this->assertTrue(Permission::has(TestAppPermission::all(), TestAppPermission::APPROVE));
        $this->assertTrue(Permission::has(TestAppPermission::all(), Permission::READ));
    }

    public function testDefaultLimitIs31Bits(): void
    {
        $this->assertSame(31, Permission::getMaxBits());
    }

    public function testHighBitIsRejectedByDefaultLimit(): void
    {
        $this->expectException(ERuntimeError::class);
        TestWidePermission::all();
    }

    public function testHighBitCanBeEnabledByRaisingLimitTo63(): void
    {
        TestWidePermission::setMaxBits(63);

        $this->assertTrue(Permission::has(TestWidePermission::all(), TestWidePermission::ARCHIVE));
    }

    public function testSetMaxBitsRejectsOutOfRangeValues(): void
    {
        $this->expectException(EInvalidArgumentException::class);
        Permission::setMaxBits(64);
    }
}
