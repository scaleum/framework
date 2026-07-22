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

namespace Scaleum\Security\Contracts;

interface SubjectMembershipLoaderInterface
{
    /**
     * Loads direct membership ids for a user.
     *
     * @param int $userId User identity id.
     * @return list<int>
     */
    public function loadDirectMembershipIds(int $userId): array;

    /**
     * Loads one direct membership id for a user.
     *
     * @param int $userId User identity id.
     * @return int
     */
    public function loadDirectMembershipId(int $userId): int;
}
