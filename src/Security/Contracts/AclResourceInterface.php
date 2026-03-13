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

interface AclResourceInterface
{
    /**
     * Returns ACL table name.
     */
    public function getAclTable(): string;

    /**
     * Returns ACL row that is already loaded via model relation.
     *
     * Return null or an empty array when ACL data is not preloaded.
     * Expected keys: owner_id, group_id, owner_perms, group_perms, other_perms.
     *
     * @return array<string, mixed>|null
     */
    public function getAclData(): ?array;

    /**
     * Defines access policy when ACL row is missing.
     */
    public function isAllowedWhenAclMissing(): bool;
}