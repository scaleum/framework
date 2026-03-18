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

/**
 * Minimal contract for a security group.
 *
 * Real implementations can be rich entities/models with additional fields.
 */
interface GroupInterface
{
    public function getId(): int;

    public function getName(): string;
}
