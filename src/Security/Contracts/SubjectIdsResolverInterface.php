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
 * Resolves ids for a typed subject member.
 */
interface SubjectIdsResolverInterface
{
    /**
     * @param list<int> $seedIds Caller-provided ids that should be included in result.
     * @return list<int>
     */
    public function resolve(int $memberType, int $memberId, array $seedIds = []): array;
}
