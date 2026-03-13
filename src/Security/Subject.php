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

final class Subject
{
    protected int $userId;

    protected array $groupIds;

    protected array $roleIds;

    public function __construct(
        int $userId,
        array $groupIds = [],
        array $roleIds = []
    ) {
        $this->userId   = $userId;
        $this->groupIds = $groupIds;
        $this->roleIds  = $roleIds;
    }

    public function getUserId(): int
    {
        return $this->userId;
    }

    public function getGroupIds(): array
    {
        return $this->groupIds;
    }

    public function getRoleIds(): array
    {
        return $this->roleIds;
    }
}
