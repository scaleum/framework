<?php

declare (strict_types = 1);
/**
 * This file is part of Scaleum Framework.
 *
 * (C) 2009-2026 Maxim Kirichenko <kirichenko.maxim@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Scaleum\Security\Services;

use Scaleum\Security\Contracts\AclResourceInterface;
use Scaleum\Security\Subject;
use Scaleum\Stdlib\Exceptions\ERuntimeError;
use Scaleum\Storages\PDO\ModelAbstract;

final class AclAccessResolver {
    public function isAllowed(
        ModelAbstract $model,
        Subject $subject,
        int $permission
    ): bool {
        return $this->isAllowedInternal($model, $subject, $permission, true);
    }

    public function isAllowedAny(
        ModelAbstract $model,
        Subject $subject,
        int $permission
    ): bool {
        return $this->isAllowedInternal($model, $subject, $permission, false);
    }

    private function isAllowedInternal(
        ModelAbstract $model,
        Subject $subject,
        int $permission,
        bool $requireAllPermissions
    ): bool {

        if (! $model instanceof AclResourceInterface) {
            return false;
        }

        if (! $model->isExisting()) {
            return false;
        }

        $acl = $this->resolveAcl($model);

        if (! $acl) {
            return $model->isAllowedWhenAclMissing();
        }

        if ((int) $acl['owner_id'] === $subject->getUserId()) {
            return $this->match((int) $acl['owner_perms'], $permission, $requireAllPermissions);
        }

        $groupId = $acl['group_id'] ?? null;

        if (
            $groupId !== null &&
            in_array((int) $groupId, $subject->getGroupIds(), true)
        ) {
            return $this->match((int) $acl['group_perms'], $permission, $requireAllPermissions);
        }

        return $this->match((int) $acl['other_perms'], $permission, $requireAllPermissions);
    }

    public function assertAllowed(
        ModelAbstract $model,
        Subject $subject,
        int $permission
    ): void {

        if (! $this->isAllowed($model, $subject, $permission)) {
            throw new ERuntimeError('Access denied');
        }
    }

    public function assertAllowedAny(
        ModelAbstract $model,
        Subject $subject,
        int $permission
    ): void {

        if (! $this->isAllowedAny($model, $subject, $permission)) {
            throw new ERuntimeError('Access denied');
        }
    }

    private function match(int $mask, int $permission, bool $requireAllPermissions = true): bool {
        if ($requireAllPermissions) {
            return ($mask & $permission) === $permission;
        }

        return ($mask & $permission) !== 0;
    }

    /**
     * Uses preloaded ACL data when available and valid, otherwise loads row from ACL table.
     *
     * @return array<string, mixed>|null
     */
    private function resolveAcl(AclResourceInterface&ModelAbstract $model): ?array {
        $acl = $model->getAclData();
        if ($this->isValidAclData($acl)) {
            return $acl;
        }

        $database = $model->getDatabase();
        AclTableGuard::assertTableExists($database, $model->getAclTable());

        $row = $database
            ->getQueryBuilder()
            ->select()
            ->from($model->getAclTable())
            ->where('record_id', $model->getId(), false)
            ->limit(1)
            ->row();

        return is_array($row) ? $row : null;
    }

    /**
     * @param array<string, mixed>|null $acl
     */
    private function isValidAclData(?array $acl): bool {
        if (empty($acl)) {
            return false;
        }

        foreach (['owner_id', 'group_id', 'owner_perms', 'group_perms', 'other_perms'] as $key) {
            if (! array_key_exists($key, $acl) || ! is_int($acl[$key])) {
                return false;
            }
        }

        return true;
    }
}
