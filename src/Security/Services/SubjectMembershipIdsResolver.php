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

use Scaleum\Security\Contracts\SubjectIdsResolverInterface;
use Scaleum\Security\Contracts\SubjectMembershipHierarchyLoaderInterface;
use Scaleum\Security\Contracts\SubjectMembershipLoaderInterface;
use Scaleum\Stdlib\Exceptions\EInvalidArgumentException;

final class SubjectMembershipIdsResolver implements SubjectIdsResolverInterface
{
    private SubjectMembershipLoaderInterface $membershipLoader;

    private ?SubjectMembershipHierarchyLoaderInterface $hierarchyLoader;

    public function __construct(
        SubjectMembershipLoaderInterface $membershipLoader,
        ?SubjectMembershipHierarchyLoaderInterface $hierarchyLoader = null
    ) {
        $this->membershipLoader = $membershipLoader;
        $this->hierarchyLoader  = $hierarchyLoader;
    }

    public function resolve(int $memberType, int $memberId, array $seedIds = []): array
    {
        if ($memberType <= 0) {
            throw new EInvalidArgumentException(sprintf('Member type must be a positive integer, got %d.', $memberType));
        }

        if ($memberId <= 0) {
            throw new EInvalidArgumentException(sprintf('Member id must be a positive integer, got %d.', $memberId));
        }

        $directIds = $this->normalizeIds(array_merge(
            $seedIds,
            $this->membershipLoader->loadDirectMembershipIds($memberType, $memberId)
        ));

        if ($this->hierarchyLoader === null || empty($directIds)) {
            return $directIds;
        }

        $visited = [];
        $queue   = $directIds;

        foreach ($directIds as $id) {
            $visited[$id] = true;
        }

        while (! empty($queue)) {
            $id = (int) array_shift($queue);
            $parentIds = $this->normalizeIds($this->hierarchyLoader->loadParentMembershipIds($id));

            foreach ($parentIds as $parentId) {
                if (isset($visited[$parentId])) {
                    continue;
                }

                $visited[$parentId] = true;
                $queue[]            = $parentId;
            }
        }

        $resolved = array_map('intval', array_keys($visited));
        sort($resolved);

        return $resolved;
    }

    /**
     * @param array<int, mixed> $ids
     * @return list<int>
     */
    private function normalizeIds(array $ids): array
    {
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
