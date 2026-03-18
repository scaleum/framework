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
namespace Scaleum\Security\Contracts;

use Scaleum\Security\Subject;
use Scaleum\Storages\PDO\Builders\Contracts\QueryBuilderInterface;

interface AclQueryApplierInterface {
    public function apply(
        QueryBuilderInterface $query,
        AclResourceInterface|string $resource,
        string $recordField,
        Subject $subject,
        int $permission
    ): void;

    public function applyAny(
        QueryBuilderInterface $query,
        AclResourceInterface|string $resource,
        string $recordField,
        Subject $subject,
        int $permission
    ): void;
}