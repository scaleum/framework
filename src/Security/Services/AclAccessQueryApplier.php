<?php

declare(strict_types=1);
/**
 * This file is part of Scaleum Framework.
 *
 * (C) 2009-2026 Maxim Kirichenko <kirichenko.maxim@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Scaleum\Security\Services;

use Scaleum\Security\Contracts\AclQueryApplierInterface;
use Scaleum\Security\Contracts\AclResourceInterface;
use Scaleum\Security\Subject;
use Scaleum\Storages\PDO\Builders\Contracts\QueryBuilderInterface;

final class AclAccessQueryApplier implements AclQueryApplierInterface
{
    public function apply(
        QueryBuilderInterface $query,
        AclResourceInterface|string $resource,
        string $recordField,
        Subject $subject,
        int $permission
    ): void {
        $this->applyInternal($query, $resource, $recordField, $subject, $permission, true);
    }

    public function applyAny(
        QueryBuilderInterface $query,
        AclResourceInterface|string $resource,
        string $recordField,
        Subject $subject,
        int $permission
    ): void {
        $this->applyInternal($query, $resource, $recordField, $subject, $permission, false);
    }

    private function applyInternal(
        QueryBuilderInterface $query,
        AclResourceInterface|string $resource,
        string $recordField,
        Subject $subject,
        int $permission,
        bool $requireAllPermissions
    ): void {
        $alias    = 'acl';
        $aclTable = $this->resolveAclTable($resource);

        if (method_exists($query, 'getDatabase')) {
            AclTableGuard::assertTableExists($query->getDatabase(), $aclTable);
        }
        
        $groupIds      = $subject->getGroupIds();
        $subjectUserId = $subject->getUserId();
        $permission    = (int) $permission;

        $query->joinInner(
            "{$aclTable} AS {$alias}",
            "{$alias}.record_id = {$recordField}"
        );

        $query->whereWrap()

            ->whereWrap()
            ->where("{$alias}.owner_id", $subjectUserId)
            ->where($this->buildPermissionCondition("{$alias}.owner_perms", $permission, $requireAllPermissions), null, false)
            ->whereWrapEnd();

        if (! empty($groupIds)) {
            $query->whereWrapOr()
                ->whereIn("{$alias}.group_id", $groupIds)
                ->where($this->buildPermissionCondition("{$alias}.group_perms", $permission, $requireAllPermissions), null, false)
                ->whereWrapEnd();
        }

        $query->whereWrapOr()
            ->where($this->buildPermissionCondition("{$alias}.other_perms", $permission, $requireAllPermissions), null, false)
            ->whereWrapEnd()

            ->whereWrapEnd();
    }

    private function resolveAclTable(AclResourceInterface|string $resource): string
    {
        if (is_string($resource)) {
            return $resource;
        }

        return $resource->getAclTable();
    }

    private function buildPermissionCondition(string $field, int $permission, bool $requireAllPermissions = true): string
    {
        if ($requireAllPermissions) {
            return "({$field} & {$permission}) = {$permission}";
        }

        return "({$field} & {$permission}) != 0";
    }
}
