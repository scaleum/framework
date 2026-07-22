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
     * Hydrates one group id for a user subject.
     *
     * @param Subject $subject User subject.
     * @param SubjectIdResolverInterface $resolver Group id resolver.
     * @return void
     */
    public function hydrateGroupIdForUser(
        Subject $subject,
        SubjectIdResolverInterface $resolver
    ): void;

    /**
     * Hydrates group ids for a user subject.
     *
     * @param Subject $subject User subject.
     * @param SubjectIdsResolverInterface $resolver Group ids resolver.
     * @param list<int> $seedIds Caller-provided ids that should be included in result.
     * @return void
     */
    public function hydrateGroupIdsForUser(
        Subject $subject,
        SubjectIdsResolverInterface $resolver,
        array $seedIds = []
    ): void;

    /**
     * Hydrates one role id for a user subject.
     *
     * @param Subject $subject User subject.
     * @param SubjectIdResolverInterface $resolver Role id resolver.
     * @return void
     */
    public function hydrateRoleIdForUser(
        Subject $subject,
        SubjectIdResolverInterface $resolver
    ): void;

    /**
     * Hydrates role ids for a user subject.
     *
     * @param Subject $subject User subject.
     * @param SubjectIdsResolverInterface $resolver Role ids resolver.
     * @param list<int> $seedIds Caller-provided ids that should be included in result.
     * @return void
     */
    public function hydrateRoleIdsForUser(
        Subject $subject,
        SubjectIdsResolverInterface $resolver,
        array $seedIds = []
    ): void;
}
