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

use Scaleum\Security\Contracts\PermissionInterface;
use Scaleum\Stdlib\Exceptions\EInvalidArgumentException;
use Scaleum\Stdlib\Exceptions\ERuntimeError;

/**
 * Bitmask permission constants and helpers.
 *
 * Designed to be extended in application projects:
 *
 * ```php
 * class AppPermission extends Permission
 * {
 *     public const APPROVE = 1 << 8;
 *     public const PUBLISH = 1 << 9;
 *
 *     protected static array $perms = Permission::BASE_LABELS + [
 *         self::APPROVE => 'Approve',
 *         self::PUBLISH => 'Publish',
 *     ];
 * }
 * ```
 *
 * Use `AppPermission::all()` to get an actual full mask from the subclass
 * registry. Unlike `BASE_ALL`, it is always synchronized with `static::$perms`.
 *
 * Static helper methods use late static binding, so calling
 * `AppPermission::label()` / `AppPermission::labels()` will automatically
 * use the subclass registry.
 *
 * Bit-limit policy:
 * - default soft limit is 31 bits (0..30)
 * - can be increased up to 63 bits on 64-bit platforms
 * - use `Permission::setMaxBits(63)` when your storage/runtime supports it
 */
class Permission implements PermissionInterface
{
    public const DEFAULT_MAX_BITS = 31;
    public const ABSOLUTE_MAX_BITS = 63;

    public const NONE = PermissionInterface::NONE;

    public const READ    = PermissionInterface::READ;
    public const WRITE   = PermissionInterface::WRITE;
    public const DELETE  = PermissionInterface::DELETE;
    public const EXECUTE = PermissionInterface::EXECUTE;
    public const PRINT   = PermissionInterface::PRINT;
    public const EXPORT  = PermissionInterface::EXPORT;
    public const IMPORT  = PermissionInterface::IMPORT;
    public const SHARE   = PermissionInterface::SHARE;

    // Base mask for framework-level permissions only.
    // In subclasses prefer static::all() to avoid manual synchronization.
    public const BASE_ALL =
    self::READ |
        self::WRITE |
        self::DELETE |
        self::EXECUTE |
        self::PRINT  |
        self::EXPORT |
        self::IMPORT |
        self::SHARE;

    /**
     * Base labels for framework-level permissions.
     * Subclasses may reuse them when declaring their own $perms:
     *   protected static array $perms = Permission::BASE_LABELS + [self::APPROVE => 'Approve'];
     *
     * @var array<int, string>
     */
    public const BASE_LABELS = [
        self::READ    => 'Read',
        self::WRITE   => 'Write',
        self::DELETE  => 'Delete',
        self::EXECUTE => 'Execute',
        self::PRINT   => 'Print',
        self::EXPORT  => 'Export',
        self::IMPORT  => 'Import',
        self::SHARE   => 'Share',
    ];

    /**
     * Human-readable labels for every bit defined in this class.
     * Override in subclasses to add project-specific bits (see class docblock).
     * Uses late static binding, so `static::$perms` resolves to the subclass.
     *
     * @var array<int, string>
     */
    protected static array $perms = self::BASE_LABELS;

    // Soft limit for allowed permission bits (count), e.g. 31 => bits 0..30.
    // Can be increased up to 63 on 64-bit platforms.
    protected static int $maxBits = self::DEFAULT_MAX_BITS;

    protected function __construct() {}

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    /**
     * Returns true when $mask contains ALL bits of $permission.
     * For NONE checks whether $mask itself is 0.
     */
    public static function has(int $mask, int $permission): bool
    {
        if ($permission === self::NONE) {
            return $mask === self::NONE;
        }

        return ($mask & $permission) === $permission;
    }

    /**
     * Returns true when $mask contains AT LEAST ONE bit of $permission.
     */
    public static function hasAny(int $mask, int $permission): bool
    {
        return ($mask & $permission) !== 0;
    }

    /**
     * Returns the human-readable label for a single permission bit,
     * or null when the bit is not registered.
     */
    public static function label(int $permission): ?string
    {
        static::assertPermissionRegistryWithinLimit();
        return static::$perms[$permission] ?? null;
    }

    /**
     * Gets the current soft limit for allowed permission bits.
     */
    public static function getMaxBits(): int
    {
        return static::$maxBits;
    }

    /**
     * Sets the soft limit for allowed permission bits.
     *
     * Examples:
     * - 31 (default): bits 0..30
     * - 63: bits 0..62 (on 64-bit platforms)
     */
    public static function setMaxBits(int $maxBits): void
    {
        $platformMaxBits = min(self::ABSOLUTE_MAX_BITS, (PHP_INT_SIZE * 8) - 1);
        if ($maxBits < 1 || $maxBits > $platformMaxBits) {
            throw new EInvalidArgumentException(
                sprintf('Permission bit limit must be in range [1..%d], got %d.', $platformMaxBits, $maxBits)
            );
        }

        static::$maxBits = $maxBits;
    }

    /**
     * Returns a full permission mask based on the current permission registry.
     * Uses late static binding — subclass registries are respected.
     */
    public static function all(): int
    {
        static::assertPermissionRegistryWithinLimit();

        $mask = self::NONE;
        foreach (array_keys(static::$perms) as $bit) {
            if ($bit !== self::NONE) {
                $mask |= (int) $bit;
            }
        }

        return $mask;
    }

    /**
     * Returns labels for every bit that is set in $mask.
     * Uses late static binding — subclass registries are respected.
     *
     * @return array<int, string>  [bit => label, ...]
     */
    public static function labels(int $mask): array
    {
        static::assertPermissionRegistryWithinLimit();

        $result = [];
        foreach (static::$perms as $bit => $name) {
            if ($bit !== self::NONE && ($mask & $bit) === $bit) {
                $result[$bit] = $name;
            }
        }

        return $result;
    }

    /**
     * Ensures all registered permission bits fit within the configured limit.
     */
    protected static function assertPermissionRegistryWithinLimit(): void
    {
        foreach (array_keys(static::$perms) as $bit) {
            $bit = (int) $bit;

            if ($bit === self::NONE) {
                continue;
            }

            if ($bit < 0 || ($bit & ($bit - 1)) !== 0) {
                throw new ERuntimeError(
                    sprintf('Permission key %d is invalid. Only single positive bit flags are allowed.', $bit)
                );
            }

            if (static::bitIndex($bit) >= static::$maxBits) {
                throw new ERuntimeError(
                    sprintf(
                        'Permission bit %d exceeds configured limit %d bits. Use %s::setMaxBits(...) to increase it.',
                        static::bitIndex($bit),
                        static::$maxBits,
                        static::class
                    )
                );
            }
        }
    }

    /**
     * Returns zero-based bit index for a single-bit positive integer.
     */
    protected static function bitIndex(int $bit): int
    {
        $index = 0;
        while ($bit > 1) {
            $bit >>= 1;
            $index++;
        }

        return $index;
    }
}
