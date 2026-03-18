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

use Scaleum\Security\Contracts\SubjectHydratorInterface;
use Scaleum\Security\Contracts\SubjectIdsResolverInterface;
use Scaleum\Security\Subject;
use Scaleum\Security\SubjectType;
use Scaleum\Stdlib\Exceptions\EInvalidArgumentException;

final class SubjectHydrator implements SubjectHydratorInterface {
    public function hydrateGroupIdsForUser(
        Subject $subject,
        SubjectIdsResolverInterface $resolver,
        array $seedIds = []
    ): void {
        $this->hydrateGroupIdsForMember($subject, $resolver, SubjectType::USER, $subject->getUserId(), $seedIds);
    }

    public function hydrateGroupIdsForMember(
        Subject $subject,
        SubjectIdsResolverInterface $resolver,
        int $memberType,
        int $memberId,
        array $seedIds = []
    ): void {
        $ids = $this->resolveIds($subject, $resolver, $memberType, $memberId, $seedIds);
        $subject->setGroupIds($ids);
    }

    public function hydrateRoleIdsForUser(
        Subject $subject,
        SubjectIdsResolverInterface $resolver,
        array $seedIds = []
    ): void {
        $this->hydrateRoleIdsForMember($subject, $resolver, SubjectType::USER, $subject->getUserId(), $seedIds);
    }

    public function hydrateRoleIdsForMember(
        Subject $subject,
        SubjectIdsResolverInterface $resolver,
        int $memberType,
        int $memberId,
        array $seedIds = []
    ): void {
        $ids = $this->resolveIds($subject, $resolver, $memberType, $memberId, $seedIds);
        $subject->setRoleIds($ids);
    }

    /**
     * @param list<int> $seedIds
     * @return list<int>
     */
    private function resolveIds(
        Subject $subject,
        SubjectIdsResolverInterface $resolver,
        int $memberType,
        int $memberId,
        array $seedIds = []
    ): array {
        $userId = $subject->getUserId();

        if ($userId <= 0) {
            throw new EInvalidArgumentException(sprintf('User id must be a positive integer, got %d.', $userId));
        }

        if ($memberType <= 0) {
            throw new EInvalidArgumentException(sprintf('Member type must be a positive integer, got %d.', $memberType));
        }

        if ($memberId <= 0) {
            throw new EInvalidArgumentException(sprintf('Member id must be a positive integer, got %d.', $memberId));
        }

        return $this->normalizeIds($resolver->resolve($memberType, $memberId, $seedIds));
    }

    /**
     * @param array<int, mixed> $ids
     * @return list<int>
     */
    private function normalizeIds(array $ids): array {
        $normalized = [];

        foreach ($ids as $id) {
            $value = (int) $id;
            if ($value > 0) {
                $normalized[$value] = true;
            }
        }

        $result = array_map('intval', array_keys($normalized));
        sort($result);

        return $result;
    }
}
