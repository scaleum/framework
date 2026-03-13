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

interface RbacLoaderInterface
{
    /**
     * Loads RBAC entries by object id.
     *
     * @return array<int, array{subject_type:int,subject_id:int,permissions:int}>
     */
    public function load(string $objectId): array;
}
