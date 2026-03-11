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

use Scaleum\Storages\PDO\ModelAbstract;
use Scaleum\Security\Permission;
use Scaleum\Security\Subject;
use Scaleum\Security\Contracts\AclResourceInterface;
final class AclAccessResolver
{
    public function isAllowed(
        ModelAbstract $model,
        Subject $subject,
        int $permission
    ): bool {

        if (!$model instanceof AclResourceInterface) {
            return false;
        }

        if (!$model->isExisting()) {
            return false;
        }

        $database = $model->getDatabase();

        $acl = $database
            ->getQueryBuilder()
            // ->prepare(true)
            ->select()
            ->from($model->getAclTable())
            ->where([
                'record_id' => $model->getId(),
            ])
            ->limit(1)
            ->row();

        if (!$acl) {
            return $model->isAllowedWhenAclMissing();
        }

        if ((int)$acl['owner_id'] === $subject->getUserId()) {
            return $this->match((int)$acl['owner_permissions'], $permission);
        }

        $groupId = $acl['group_id'] ?? null;

        if (
            $groupId !== null &&
            in_array((int)$groupId, $subject->getGroupIds(), true)
        ) {
            return $this->match((int)$acl['group_permissions'], $permission);
        }

        return $this->match((int)$acl['other_permissions'], $permission);
    }

    public function assertAllowed(
        ModelAbstract $model,
        Subject $subject,
        int $permission
    ): void {

        if (!$this->isAllowed($model, $subject, $permission)) {
            throw new \RuntimeException('Access denied');
        }
    }

    private function match(int $mask, int $permission): bool
    {
        return ($mask & $permission) === $permission;
    }
}