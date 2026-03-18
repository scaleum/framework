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

use Scaleum\Security\Subject;

interface SubjectHydratorInterface
{
    /**
     * Hydrates subject group ids for default USER-based identity.
     *
     * @param list<int> $seedIds
     */
    public function hydrateGroupIdsForUser(
        Subject $subject,
        SubjectIdsResolverInterface $resolver,
        array $seedIds = []
    ): void;

    /**
     * Hydrates subject group ids for typed identity tuple (member_type, member_id).
     *
     * @param list<int> $seedIds
     */
    public function hydrateGroupIdsForMember(
        Subject $subject,
        SubjectIdsResolverInterface $resolver,
        int $memberType,
        int $memberId,
        array $seedIds = []
    ): void;

    /**
     * Hydrates subject role ids for default USER-based identity.
     *
     * @param list<int> $seedIds
     */
    public function hydrateRoleIdsForUser(
        Subject $subject,
        SubjectIdsResolverInterface $resolver,
        array $seedIds = []
    ): void;

    /**
     * Hydrates subject role ids for typed identity tuple (member_type, member_id).
     *
     * @param list<int> $seedIds
     */
    public function hydrateRoleIdsForMember(
        Subject $subject,
        SubjectIdsResolverInterface $resolver,
        int $memberType,
        int $memberId,
        array $seedIds = []
    ): void;
}
