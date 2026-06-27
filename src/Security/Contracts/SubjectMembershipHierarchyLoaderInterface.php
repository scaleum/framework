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

interface SubjectMembershipHierarchyLoaderInterface
{
    /**
     * Loads parent|descendant membership ids for a child membership id.
     *
     * @return list<int>
     */
    public function loadMembershipIds(int $membershipId): array;
}
