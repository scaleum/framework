<?php
declare(strict_types=1);

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Scaleum\Security\Contracts\RbacLoaderInterface;
use Scaleum\Security\Permission;
use Scaleum\Security\Services\RbacAccessResolver;
use Scaleum\Security\Subject;
use Scaleum\Security\SubjectType;

final class RbacServicesTest extends TestCase
{
    public function testAssertAllowedThrowsWhenPermissionsAreMissing(): void
    {
        $subject = new Subject(10, [], []);

        $resolver = new RbacAccessResolver();
        $resolver->seed('obj-assert-1', [
            ['subject_type' => SubjectType::USER, 'subject_id' => 10, 'permissions' => Permission::READ],
        ]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Access denied');

        $resolver->assertAllowed('obj-assert-1', $subject, Permission::READ | Permission::WRITE);
    }

    public function testAssertAllowedAnyDoesNotThrowWhenAnyPermissionMatches(): void
    {
        $subject = new Subject(10, [], []);

        $resolver = new RbacAccessResolver();
        $resolver->seed('obj-assert-2', [
            ['subject_type' => SubjectType::USER, 'subject_id' => 10, 'permissions' => Permission::READ],
        ]);

        $resolver->assertAllowedAny('obj-assert-2', $subject, Permission::READ | Permission::WRITE);

        $this->assertTrue(true);
    }

    public function testResolverMergesPermissionsFromUserGroupAndRole(): void
    {
        $subject = new Subject(10, [2], [7]);

        $resolver = new RbacAccessResolver();
        $resolver->seed('obj-1', [
            ['subject_type' => SubjectType::USER, 'subject_id' => 10, 'permissions' => Permission::READ],
            ['subject_type' => SubjectType::GROUP, 'subject_id' => 2, 'permissions' => Permission::WRITE],
            ['subject_type' => SubjectType::ROLE, 'subject_id' => 7, 'permissions' => Permission::DELETE],
        ]);

        $this->assertTrue($resolver->isAllowed('obj-1', $subject, Permission::READ | Permission::WRITE | Permission::DELETE));
    }

    public function testIsAllowedRequiresAllPermissions(): void
    {
        $subject = new Subject(10, [2], []);

        $resolver = new RbacAccessResolver();
        $resolver->seed('obj-2', [
            ['subject_type' => SubjectType::USER, 'subject_id' => 10, 'permissions' => Permission::READ],
        ]);

        $this->assertFalse($resolver->isAllowed('obj-2', $subject, Permission::READ | Permission::WRITE));
    }

    public function testIsAllowedAnyMatchesAtLeastOnePermission(): void
    {
        $subject = new Subject(10, [2], []);

        $resolver = new RbacAccessResolver();
        $resolver->seed('obj-3', [
            ['subject_type' => SubjectType::USER, 'subject_id' => 10, 'permissions' => Permission::READ],
        ]);

        $this->assertTrue($resolver->isAllowedAny('obj-3', $subject, Permission::READ | Permission::WRITE));
    }

    public function testResolverUsesLazyLoadOncePerObjectIdAndSupportsMultipleSubjects(): void
    {
        $subjectA = new Subject(10, [], []);
        $subjectB = new Subject(11, [], []);

        /** @var MockObject&RbacLoaderInterface $loader */
        $loader = $this->createMock(RbacLoaderInterface::class);
        $loader->expects($this->once())
            ->method('load')
            ->with('obj-4')
            ->willReturn([
                ['subject_type' => SubjectType::USER, 'subject_id' => 10, 'permissions' => Permission::READ],
                ['subject_type' => SubjectType::USER, 'subject_id' => 11, 'permissions' => Permission::WRITE],
            ]);

        $resolver = new RbacAccessResolver($loader);

        $this->assertTrue($resolver->isAllowed('obj-4', $subjectA, Permission::READ));
        $this->assertFalse($resolver->isAllowed('obj-4', $subjectA, Permission::WRITE));

        $this->assertTrue($resolver->isAllowed('obj-4', $subjectB, Permission::WRITE));
        $this->assertFalse($resolver->isAllowed('obj-4', $subjectB, Permission::READ));
    }
}
