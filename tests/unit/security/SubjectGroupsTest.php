<?php

declare (strict_types = 1);

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Scaleum\Security\Contracts\SubjectIdsResolverInterface;
use Scaleum\Security\Contracts\SubjectMembershipHierarchyLoaderInterface;
use Scaleum\Security\Contracts\SubjectMembershipLoaderInterface;
use Scaleum\Security\Services\SubjectMembershipIdsResolver;
use Scaleum\Security\Services\SubjectHydrator;
use Scaleum\Security\Subject;
use Scaleum\Security\SubjectType;
use Scaleum\Stdlib\Exceptions\EInvalidArgumentException;
use Scaleum\Storages\PDO\Builders\Adapters\SQLite\Query as SQLiteQueryBuilder;
use Scaleum\Storages\PDO\Builders\Contracts\QueryBuilderInterface;
use Scaleum\Storages\PDO\Database;

final class SubjectGroupsTest extends TestCase {
    public function testResolverReturnsDirectGroupIdsWhenHierarchyLoaderIsNotProvided(): void {
        $membershipLoader = new class() implements SubjectMembershipLoaderInterface {
            public function loadDirectMembershipIds(int $memberType, int $memberId): array {
                return [4, 4, 2, '6', 0, -1];
            }
        };

        $resolver = new SubjectMembershipIdsResolver($membershipLoader);

        $this->assertSame([2, 4, 6], $resolver->resolve(SubjectType::USER, 10));
    }

    public function testResolverMergesSeedIdsWithLoadedDirectGroups(): void {
        $membershipLoader = new class() implements SubjectMembershipLoaderInterface {
            public function loadDirectMembershipIds(int $memberType, int $memberId): array {
                return [4, 2];
            }
        };

        $resolver = new SubjectMembershipIdsResolver($membershipLoader);

        $this->assertSame([2, 4, 7], $resolver->resolve(SubjectType::USER, 10, [7, 2]));
    }

    public function testResolverExpandsInheritedGroupsAndSkipsCycles(): void {
        $membershipLoader = new class() implements SubjectMembershipLoaderInterface {
            public function loadDirectMembershipIds(int $memberType, int $memberId): array {
                return [10];
            }
        };

        $hierarchyLoader = new class() implements SubjectMembershipHierarchyLoaderInterface {
            public function loadParentMembershipIds(int $membershipId): array {
                $groupId = $membershipId;
                return match ($groupId) {
                    10      => [20],
                    20      => [30],
                    30      => [10],
                    default => [],
                };
            }
        };

        $resolver = new SubjectMembershipIdsResolver($membershipLoader, $hierarchyLoader);

        $this->assertSame([10, 20, 30], $resolver->resolve(SubjectType::USER, 99));
    }

    public function testResolverThrowsForInvalidMemberIdentityTuple(): void {
        $membershipLoader = new class() implements SubjectMembershipLoaderInterface {
            public function loadDirectMembershipIds(int $memberType, int $memberId): array {
                return [];
            }
        };

        $resolver = new SubjectMembershipIdsResolver($membershipLoader);

        $this->expectException(EInvalidArgumentException::class);
        $resolver->resolve(0, 10);
    }

    public function testHydratorCreatesSubjectForUserUsingResolvedGroupsAndSeedRoles(): void {
        $membershipLoader = new class() implements SubjectMembershipLoaderInterface {
            public function loadDirectMembershipIds(int $memberType, int $memberId): array {
                return $memberType === SubjectType::USER && $memberId === 15 ? [2, 8] : [];
            }
        };

        $roleResolver = new class() implements SubjectIdsResolverInterface {
            public function resolve(int $memberType, int $memberId, array $seedIds = []): array {
                return $seedIds;
            }
        };

        $resolver = new SubjectMembershipIdsResolver($membershipLoader);
        $hydrator = new SubjectHydrator();
        $subject  = new Subject(15);

        $hydrator->hydrateGroupIdsForUser($subject, $resolver);
        $hydrator->hydrateRoleIdsForUser($subject, $roleResolver, [7, 7, 3]);

        $this->assertSame(15, $subject->getUserId());
        $this->assertSame([2, 8], $subject->getGroupIds());
        $this->assertSame([3, 7], $subject->getRoleIds());
    }

    public function testHydratorCreatesSubjectForTypedMember(): void {
        $membershipLoader = new class() implements SubjectMembershipLoaderInterface {
            public function loadDirectMembershipIds(int $memberType, int $memberId): array {
                if ($memberType === SubjectType::ROLE && $memberId === 50) {
                    return [100];
                }

                return [];
            }
        };

        $resolver     = new SubjectMembershipIdsResolver($membershipLoader);
        $roleResolver = new class() implements SubjectIdsResolverInterface {
            public function resolve(int $memberType, int $memberId, array $seedIds = []): array {
                return $seedIds;
            }
        };

        $hydrator = new SubjectHydrator();
        $subject  = new Subject(1);

        $hydrator->hydrateGroupIdsForMember($subject, $resolver, SubjectType::ROLE, 50);
        $hydrator->hydrateRoleIdsForMember($subject, $roleResolver, SubjectType::ROLE, 50, [9]);

        $this->assertSame(1, $subject->getUserId());
        $this->assertSame([100], $subject->getGroupIds());
        $this->assertSame([9], $subject->getRoleIds());
    }

    public function testHydratorCanResolveEffectiveRolesViaRoleResolver(): void {
        $membershipLoader = new class() implements SubjectMembershipLoaderInterface {
            public function loadDirectMembershipIds(int $memberType, int $memberId): array {
                return [5];
            }
        };

        $roleResolver = new class() implements SubjectIdsResolverInterface {
            public function resolve(int $memberType, int $memberId, array $seedIds = []): array {
                return array_merge($seedIds, [20, 10, 20]);
            }
        };

        $groupResolver = new SubjectMembershipIdsResolver($membershipLoader);
        $hydrator      = new SubjectHydrator();
        $subject       = new Subject(33);

        $hydrator->hydrateGroupIdsForUser($subject, $groupResolver);
        $hydrator->hydrateRoleIdsForUser($subject, $roleResolver, [30, 10]);

        $this->assertSame([5], $subject->getGroupIds());
        $this->assertSame([10, 20, 30], $subject->getRoleIds());
    }

    public function testHydratorBuildsSubjectForUser321WithDefaultGroup743AndThreeNestedLevels(): void {
        $membershipLoader = new class() implements SubjectMembershipLoaderInterface {
            public function loadDirectMembershipIds(int $memberType, int $memberId): array {
                if ($memberType === SubjectType::USER && $memberId === 321) {
                    // Default user group loaded from persistence layer.
                    return [743];
                }

                return [];
            }
        };

        $hierarchyLoader = new class() implements SubjectMembershipHierarchyLoaderInterface {
            public function loadParentMembershipIds(int $membershipId): array {
                $groupId = $membershipId;
                return match ($groupId) {
                    743     => [800],
                    800     => [900],
                    900     => [1000],
                    default => [],
                };
            }
        };

        $groupResolver = new SubjectMembershipIdsResolver($membershipLoader, $hierarchyLoader);
        $hydrator      = new SubjectHydrator();
        $subject       = new Subject(321);

        $hydrator->hydrateGroupIdsForUser($subject, $groupResolver);

        $this->assertSame(321, $subject->getUserId());
        $this->assertSame([743, 800, 900, 1000], $subject->getGroupIds());
        $this->assertSame([], $subject->getRoleIds());
    }

    public function testHydratorCanUseQueryBuilderBasedResolver(): void {
        /** @var MockObject&QueryBuilderInterface $query */
        $query = $this->createMock(QueryBuilderInterface::class);
        $query->expects($this->once())->method('select')->with('group_id', true)->willReturnSelf();
        $query->expects($this->once())->method('from')->with('security_group_membership')->willReturnSelf();
        $query->expects($this->exactly(2))->method('where')->willReturnSelf();
        $query->expects($this->once())->method('rows')->willReturn([
            ['group_id' => 743],
            ['group_id' => '800'],
            ['group_id' => 743],
            ['group_id' => 0],
        ]);

        $resolver = new class($query) implements SubjectIdsResolverInterface {
            private QueryBuilderInterface $query;

            public function __construct(QueryBuilderInterface $query) {
                $this->query = $query;
            }

            public function resolve(int $memberType, int $memberId, array $seedIds = []): array {
                $rows = $this->query
                    ->select('group_id')
                    ->from('security_group_membership')
                    ->where('member_type', $memberType, false)
                    ->where('member_id', $memberId, false)
                    ->rows();

                $ids = $seedIds;
                foreach ((array) $rows as $row) {
                    if (! is_array($row)) {
                        continue;
                    }

                    $ids[] = (int) ($row['group_id'] ?? 0);
                }

                return $ids;
            }
        };

        $subject  = new Subject(321);
        $hydrator = new SubjectHydrator();

        $hydrator->hydrateGroupIdsForUser($subject, $resolver, [900]);

        $this->assertSame([743, 800, 900], $subject->getGroupIds());
        $this->assertSame([], $subject->getRoleIds());
    }

    public function testHydratorCanUseHierarchicalQueryBuilderBasedResolver(): void {
        $pdo = new \PDO('sqlite::memory:');
        $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        $this->seedHierarchyFixture($pdo);

        /** @var MockObject&Database $database */
        $database = $this->getMockBuilder(Database::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getPDO'])
            ->getMock();

        $database->method('getPDO')->willReturn($pdo);

        $query = new SQLiteQueryBuilder($database);
        $resolver = $this->createHierarchicalQueryBuilderResolver($query, $pdo);

        $subject  = new Subject(321);
        $hydrator = new SubjectHydrator();

        $hydrator->hydrateGroupIdsForUser($subject, $resolver, [700, 900, 9999]);

        $this->assertSame([743, 800, 900, 1000], $subject->getGroupIds());
        $this->assertNotContains(700, $subject->getGroupIds());
        $this->assertNotContains(9999, $subject->getGroupIds());
        $this->assertSame([], $subject->getRoleIds());

        $sql = $resolver->getLastSql();
        $this->assertIsString($sql);
        $this->assertStringContainsString('WITH RECURSIVE', $sql);
        $this->assertStringContainsString('security_group_membership', $sql);
        $this->assertStringContainsString('security_groups', $sql);
        $this->assertStringContainsString('SELECT DISTINCT group_id', $sql);
        $this->assertStringNotContainsString('child_group_id', $sql);
    }

    private function createHierarchicalQueryBuilderResolver(QueryBuilderInterface $query, \PDO $pdo): object {
        return new class($query, $pdo) implements SubjectIdsResolverInterface {
            private QueryBuilderInterface $query;

            private \PDO $pdo;

            private string $lastSql = '';

            public function __construct(QueryBuilderInterface $query, \PDO $pdo) {
                $this->query = $query;
                $this->pdo = $pdo;
            }

            public function resolve(int $memberType, int $memberId, array $seedIds = []): array {
                $cteSql = sprintf(
                    "SELECT g.group_id, g.parent_group_id\n"
                    . "FROM security_group_membership gm\n"
                    . "INNER JOIN security_groups g ON g.group_id = gm.group_id\n"
                    . "WHERE gm.member_type = %d\n"
                    . "  AND gm.member_id = %d\n"
                    . "UNION ALL\n"
                    . "SELECT p.group_id, p.parent_group_id\n"
                    . "FROM security_groups p\n"
                    . "INNER JOIN group_tree gt ON gt.parent_group_id = p.group_id",
                    $memberType,
                    $memberId
                );

                $this->lastSql = (string) $this->query
                    ->prepare(true)
                    ->withRecursive('group_tree', $cteSql, ['group_id', 'parent_group_id'])
                    ->select('DISTINCT group_id', false)
                    ->from('group_tree')
                    ->rows();

                $ids = [];
                foreach ($seedIds as $seedId) {
                    $id = (int) $seedId;
                    if ($id > 0 && $this->isExistingGroupId($id)) {
                        $ids[$id] = true;
                    }
                }

                /** @var array<int, array<string, mixed>> $rows */
                $rows = $this->pdo->query($this->lastSql)->fetchAll(\PDO::FETCH_ASSOC);

                foreach ($rows as $row) {
                    if (! is_array($row)) {
                        continue;
                    }

                    $groupId = (int) ($row['group_id'] ?? 0);
                    if ($groupId > 0) {
                        $ids[$groupId] = true;
                    }
                }

                return array_map('intval', array_keys($ids));
            }

            public function getLastSql(): string {
                return $this->lastSql;
            }

            /**
             * Ensures external seed ids are accepted only when present in groups table.
             */
            private function isExistingGroupId(int $groupId): bool {
                $stmt = $this->pdo->query(sprintf(
                    'SELECT 1 FROM security_groups WHERE group_id = %d LIMIT 1',
                    $groupId
                ));

                if ($stmt === false) {
                    return false;
                }

                return (int) $stmt->fetchColumn() === 1;
            }
        };
    }

    private function seedHierarchyFixture(\PDO $pdo): void {
        $pdo->exec('CREATE TABLE security_groups (group_id INTEGER PRIMARY KEY, parent_group_id INTEGER NULL, name TEXT)');
        $pdo->exec('CREATE TABLE security_group_membership (member_type INTEGER NOT NULL, member_id INTEGER NOT NULL, group_id INTEGER NOT NULL)');

        $pdo->exec("INSERT INTO security_groups (group_id, parent_group_id, name) VALUES (743, 800, 'G743')");
        $pdo->exec("INSERT INTO security_groups (group_id, parent_group_id, name) VALUES (800, 900, 'G800')");
        $pdo->exec("INSERT INTO security_groups (group_id, parent_group_id, name) VALUES (900, 1000, 'G900')");
        $pdo->exec("INSERT INTO security_groups (group_id, parent_group_id, name) VALUES (1000, NULL, 'G1000')");

        $pdo->exec(sprintf(
            'INSERT INTO security_group_membership (member_type, member_id, group_id) VALUES (%d, 321, 743)',
            SubjectType::USER
        ));
    }
}
