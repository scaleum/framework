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

namespace Scaleum\Security;

use Scaleum\Stdlib\Base\AttributeContainer;

final class Subject extends AttributeContainer
{
    /**
     * @return int
     */
    public function getUserId(): int
    {
        return $this->getAttribute('user_id', 0);
    }

    /**
     * @param int $userId User identity id.
     * @return void
     */
    public function setUserId(int $userId): void
    {
        $this->setAttribute('user_id', $userId);
    }

    /**
     * @return int
     */
    public function getGroupId(): int
    {
        return $this->getAttribute('group_id', 0);
    }

    /**
     * @param int $groupId Group id.
     * @return void
     */
    public function setGroupId(int $groupId): void
    {
        $this->setAttribute('group_id', $groupId);
    }

    /**
     * @return list<int>
     */
    public function getGroupIds(): array
    {
        return (array) $this->getAttribute('group_ids', []);
    }

    /**
     * @param list<int> $groupIds Group ids.
     * @return void
     */
    public function setGroupIds(array $groupIds): void
    {
        $this->setAttribute('group_ids', $groupIds);
    }

    /**
     * @return int
     */
    public function getRoleId(): int
    {
        return $this->getAttribute('role_id', 0);
    }

    /**
     * @param int $roleId Role id.
     * @return void
     */
    public function setRoleId(int $roleId): void
    {
        $this->setAttribute('role_id', $roleId);
    }

    /**
     * @return list<int>
     */
    public function getRoleIds(): array
    {
        return (array) $this->getAttribute('role_ids', []);
    }

    /**
     * @param list<int> $roleIds Role ids.
     * @return void
     */
    public function setRoleIds(array $roleIds): void
    {
        $this->setAttribute('role_ids', $roleIds);
    }
}
