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

use Scaleum\Security\Contracts\RbacLoaderInterface;
use Scaleum\Security\Subject;
use Scaleum\Security\SubjectType;
use Scaleum\Stdlib\Exceptions\ERuntimeError;

final class RbacAccessResolver
{
    private ?RbacLoaderInterface $loader;

    /** @var array<string, array<int, array{subject_type:int,subject_id:int,permissions:int}>> */
    private array $entriesCache = [];

    /** @var array<string, array<string, int>> */
    private array $subjectMaskCache = [];

    /** @var array<string, true> */
    private array $loaded = [];

    public function __construct(?RbacLoaderInterface $loader = null)
    {
        $this->loader = $loader;
    }

    /**
     * @param array<int, array{subject_type:int,subject_id:int,permissions:int}> $entries
     */
    public function seed(string $objectId, array $entries): void
    {
        $this->entriesCache[$objectId] = array_values($entries);
        unset($this->subjectMaskCache[$objectId]);
        $this->loaded[$objectId] = true;
    }

    public function clear(?string $objectId = null): void
    {
        if ($objectId === null) {
            $this->entriesCache     = [];
            $this->subjectMaskCache = [];
            $this->loaded           = [];
            return;
        }

        unset($this->entriesCache[$objectId]);
        unset($this->subjectMaskCache[$objectId]);
        unset($this->loaded[$objectId]);
    }

    public function isAllowed(string $objectId, Subject $subject, int $permission): bool
    {
        return $this->isAllowedInternal($objectId, $subject, $permission, true);
    }

    public function isAllowedAny(string $objectId, Subject $subject, int $permission): bool
    {
        return $this->isAllowedInternal($objectId, $subject, $permission, false);
    }

    public function assertAllowed(string $objectId, Subject $subject, int $permission): void
    {
        if (! $this->isAllowed($objectId, $subject, $permission)) {
            throw new ERuntimeError('Access denied');
        }
    }

    public function assertAllowedAny(string $objectId, Subject $subject, int $permission): void
    {
        if (! $this->isAllowedAny($objectId, $subject, $permission)) {
            throw new ERuntimeError('Access denied');
        }
    }

    private function isAllowedInternal(
        string $objectId,
        Subject $subject,
        int $permission,
        bool $requireAllPermissions
    ): bool {
        $mask = $this->resolveEffectiveMask($objectId, $subject);

        if ($requireAllPermissions) {
            return ($mask & $permission) === $permission;
        }

        return ($mask & $permission) !== 0;
    }

    private function resolveEffectiveMask(string $objectId, Subject $subject): int
    {
        $subjectKey = $this->buildSubjectKey($subject);
        if (isset($this->subjectMaskCache[$objectId][$subjectKey])) {
            return $this->subjectMaskCache[$objectId][$subjectKey];
        }

        $entries = $this->resolveEntries($objectId);
        $mask    = 0;

        foreach ($entries as $entry) {
            if ($this->isEntryMatched($entry, $subject)) {
                $mask |= (int) $entry['permissions'];
            }
        }

        $this->subjectMaskCache[$objectId][$subjectKey]  = $mask;
        return $mask;
    }

    /**
     * @return array<int, array{subject_type:int,subject_id:int,permissions:int}>
     */
    private function resolveEntries(string $objectId): array
    {
        if (isset($this->entriesCache[$objectId])) {
            return $this->entriesCache[$objectId];
        }

        if (isset($this->loaded[$objectId])) {
            return [];
        }

        $entries = [];
        if ($this->loader !== null) {
            $entries = $this->loader->load($objectId);
        }

        $this->entriesCache[$objectId] = array_values($entries);
        $this->loaded[$objectId]       = true;

        return $this->entriesCache[$objectId];
    }

    /**
     * @param array{subject_type:int,subject_id:int,permissions:int} $entry
     */
    private function isEntryMatched(array $entry, Subject $subject): bool
    {
        $subjectType = (int) ($entry['subject_type'] ?? 0);
        $subjectId   = (int) ($entry['subject_id'] ?? 0);

        if ($subjectType === SubjectType::USER) {
            return $subjectId === $subject->getUserId();
        }

        if ($subjectType === SubjectType::GROUP) {
            foreach ($subject->getGroupIds() as $groupId) {
                if ($subjectId === (int) $groupId) {
                    return true;
                }
            }
            return false;
        }

        if ($subjectType === SubjectType::ROLE) {
            foreach ($subject->getRoleIds() as $roleId) {
                if ($subjectId === (int) $roleId) {
                    return true;
                }
            }
            return false;
        }

        return false;
    }

    private function buildSubjectKey(Subject $subject): string
    {
        $groupIds = array_map('intval', $subject->getGroupIds());
        $roleIds  = array_map('intval', $subject->getRoleIds());

        sort($groupIds);
        sort($roleIds);

        return implode('|', [
            'u:' . $subject->getUserId(),
            'g:' . implode(',', $groupIds),
            'r:' . implode(',', $roleIds),
        ]);
    }
}
