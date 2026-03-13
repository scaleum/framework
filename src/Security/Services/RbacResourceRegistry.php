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

use Scaleum\Security\Contracts\RbacResourceInterface;

final class RbacResourceRegistry
{
    /** @var array<string, array{id:string,name:string,description:?string,permissions:list<int>,class:class-string<RbacResourceInterface>|null}> */
    private array $definitionsById = [];

    /** @var array<string, class-string<RbacResourceInterface>> */
    private array $resourcesById = [];

    /** @var array<class-string<RbacResourceInterface>, string> */
    private array $resourceIdsByClass = [];

    /**
     * @param class-string<RbacResourceInterface> $resourceClass
     */
    public function register(string $resourceClass): void
    {
        if (! is_subclass_of($resourceClass, RbacResourceInterface::class)) {
            throw new \InvalidArgumentException(sprintf(
                'Resource class `%s` must implement `%s`.',
                $resourceClass,
                RbacResourceInterface::class
            ));
        }

        $this->registerDefinition([
            'id'          => $resourceClass::getId(),
            'name'        => $resourceClass::getName(),
            'description' => $resourceClass::getDescription(),
            'permissions' => $resourceClass::getSupportedPermissions(),
        ], $resourceClass);
    }

    /**
     * @param array<int, class-string<RbacResourceInterface>> $resourceClasses
     */
    public function registerMany(array $resourceClasses): void
    {
        foreach ($resourceClasses as $resourceClass) {
            $this->register($resourceClass);
        }
    }

    /**
     * @param array{id:string,name:string,description?:?string,permissions:list<int>} $definition
     * @param class-string<RbacResourceInterface>|null $resourceClass
     */
    public function registerDefinition(array $definition, ?string $resourceClass = null): void
    {
        $resourceId = trim((string) ($definition['id'] ?? ''));
        if ($resourceId == '') {
            throw new \InvalidArgumentException('RBAC resource definition returned empty id.');
        }

        $name = trim((string) ($definition['name'] ?? ''));
        if ($name == '') {
            throw new \InvalidArgumentException(sprintf(
                'RBAC resource definition `%s` returned empty name.',
                $resourceId
            ));
        }

        $description = isset($definition['description']) ? (string) $definition['description'] : null;

        /** @var mixed $permissionsRaw */
        $permissionsRaw = $definition['permissions'] ?? [];
        if (! is_array($permissionsRaw)) {
            throw new \InvalidArgumentException(sprintf(
                'RBAC resource definition `%s` returned invalid permissions payload.',
                $resourceId
            ));
        }

        $permissions = array_map('intval', array_values($permissionsRaw));

        $existingClass = $this->resourcesById[$resourceId] ?? null;
        if ($resourceClass !== null && $existingClass !== null && $existingClass !== $resourceClass) {
            throw new \RuntimeException(sprintf(
                'Duplicate RBAC resource id `%s` for classes `%s` and `%s`.',
                $resourceId,
                $existingClass,
                $resourceClass
            ));
        }

        $this->definitionsById[$resourceId] = [
            'id'          => $resourceId,
            'name'        => $name,
            'description' => $description,
            'permissions' => $permissions,
            'class'       => $resourceClass,
        ];

        if ($resourceClass !== null) {
            $this->resourcesById[$resourceId]         = $resourceClass;
            $this->resourceIdsByClass[$resourceClass] = $resourceId;
        }
    }

    /**
     * @param array<int, array{id:string,name:string,description?:?string,permissions:list<int>}> $definitions
     */
    public function registerDefinitions(array $definitions): void
    {
        foreach ($definitions as $definition) {
            $this->registerDefinition($definition);
        }
    }

    public function has(string $resourceId): bool
    {
        return isset($this->definitionsById[$resourceId]);
    }

    /**
     * @return class-string<RbacResourceInterface>
     */
    public function get(string $resourceId): string
    {
        if (! isset($this->definitionsById[$resourceId])) {
            throw new \OutOfBoundsException(sprintf('RBAC resource id `%s` is not registered.', $resourceId));
        }

        if (! isset($this->resourcesById[$resourceId])) {
            throw new \OutOfBoundsException(sprintf(
                'RBAC resource id `%s` is registered as a definition but has no bound class.',
                $resourceId
            ));
        }

        return $this->resourcesById[$resourceId];
    }

    /**
     * @return array<string, class-string<RbacResourceInterface>>
     */
    public function all(): array
    {
        return $this->resourcesById;
    }

    /**
     * @return array<string, array{id:string,name:string,description:?string,permissions:list<int>,class:class-string<RbacResourceInterface>|null}>
     */
    public function allDefinitions(): array
    {
        return $this->definitionsById;
    }

    /**
     * @param class-string<RbacResourceInterface> $resourceClass
     */
    public function getIdByClass(string $resourceClass): string
    {
        if (! isset($this->resourceIdsByClass[$resourceClass])) {
            throw new \OutOfBoundsException(sprintf('RBAC resource class `%s` is not registered.', $resourceClass));
        }

        return $this->resourceIdsByClass[$resourceClass];
    }

    /**
     * @return array{ id:string, name:string, description:?string, permissions:list<int> }
     */
    public function describe(string $resourceId): array
    {
        if (! isset($this->definitionsById[$resourceId])) {
            throw new \OutOfBoundsException(sprintf('RBAC resource id `%s` is not registered.', $resourceId));
        }

        $definition = $this->definitionsById[$resourceId];

        return [
            'id'          => $resourceId,
            'name'        => $definition['name'],
            'description' => $definition['description'],
            'permissions' => $definition['permissions'],
        ];
    }

    /**
     * Compares current registry with another one.
     *
     * Typical usage:
     * - current registry = resources declared in code now
     * - $other registry = persisted/legacy snapshot
     *
     * Result structure:
     * - onlyInCurrent: resources present only in current registry.
     * - onlyInOther: resources present only in compared registry.
     * - outdatedInOther: alias of onlyInOther for explicit business meaning
     *   (legacy resources that are missing in current registry).
     * - classMismatches: resources with same id in both registries but
     *   bound to different classes.
     *
     * Buckets contain copies of original resource definitions to simplify consumption.
     *
     * @return array{
     *   onlyInCurrent:list<array{id:string,name:string,description:?string,permissions:list<int>,class:class-string<RbacResourceInterface>|null}>,
     *   onlyInOther:list<array{id:string,name:string,description:?string,permissions:list<int>,class:class-string<RbacResourceInterface>|null}>,
     *   outdatedInOther:list<array{id:string,name:string,description:?string,permissions:list<int>,class:class-string<RbacResourceInterface>|null}>,
     *   classMismatches:list<array{
     *     current:array{id:string,name:string,description:?string,permissions:list<int>,class:class-string<RbacResourceInterface>|null},
     *     other:array{id:string,name:string,description:?string,permissions:list<int>,class:class-string<RbacResourceInterface>|null}
     *   }>
     * }
     */
    public function compareWith(self $other): array
    {
        $currentIds = array_keys($this->definitionsById);
        $otherIds   = array_keys($other->definitionsById);

        $onlyInCurrentIds = array_values(array_diff($currentIds, $otherIds));
        $onlyInOtherIds   = array_values(array_diff($otherIds, $currentIds));

        sort($onlyInCurrentIds);
        sort($onlyInOtherIds);

        $onlyInCurrent = array_map(fn(string $id): array => $this->definitionsById[$id], $onlyInCurrentIds);

        $onlyInOther = array_map(fn(string $id): array => $other->definitionsById[$id], $onlyInOtherIds);

        $classMismatches = [];
        foreach (array_intersect($currentIds, $otherIds) as $resourceId) {
            $currentClass = $this->definitionsById[$resourceId]['class'];
            $otherClass   = $other->definitionsById[$resourceId]['class'];

            if ($currentClass !== null && $otherClass !== null && $currentClass !== $otherClass) {
                $classMismatches[] = [
                    'current' => $this->definitionsById[$resourceId],
                    'other'   => $other->definitionsById[$resourceId],
                ];
            }
        }

        usort(
            $classMismatches,
            fn(array $a, array $b): int => strcmp((string) $a['current']['id'], (string) $b['current']['id'])
        );

        return [
            // IDs that exist only in current registry snapshot.
            'onlyInCurrent'   => $onlyInCurrent,

            // IDs that exist only in compared registry snapshot.
            'onlyInOther'     => $onlyInOther,

            // Legacy/outdated IDs: present in compared snapshot, missing in current.
            'outdatedInOther' => $onlyInOther,

            // Same resource ID in both snapshots, but bound to different classes.
            'classMismatches' => $classMismatches,
        ];
    }

    /**
     * @return list<string>
     */
    public function getOutdatedInOther(self $other): array
    {
        $diff = $this->compareWith($other);
        return array_values(array_map(static fn(array $item): string => (string) $item['id'], $diff['outdatedInOther']));
    }
}
