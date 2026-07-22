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

use Scaleum\Security\Contracts\SubjectIdResolverInterface;
use Scaleum\Security\Contracts\SubjectMembershipLoaderInterface;
use Scaleum\Stdlib\Exceptions\EInvalidArgumentException;

final class SubjectMembershipIdResolver implements SubjectIdResolverInterface
{
    private SubjectMembershipLoaderInterface $membershipLoader;

    public function __construct(SubjectMembershipLoaderInterface $membershipLoader)
    {
        $this->membershipLoader = $membershipLoader;
    }

    public function resolve(int $userId): int
    {
        if ($userId <= 0) {
            throw new EInvalidArgumentException(sprintf('User id must be a positive integer, got %d.', $userId));
        }

        $id = (int) $this->membershipLoader->loadDirectMembershipId($userId);

        return $id > 0 ? $id : 0;
    }
}
