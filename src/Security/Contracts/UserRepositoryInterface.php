<?php
declare (strict_types = 1);
/**
 * This file is part of Scaleum Framework.
 *
 * (C) 2009-2025 Maxim Kirichenko <kirichenko.maxim@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Scaleum\Security\Contracts;

/**
 * UserRepositoryInterface
 *
 * @author Maxim Kirichenko <kirichenko.maxim@gmail.com>
 */
interface UserRepositoryInterface {
    /**
     * Find a user by their unique numeric ID.
     *
     * Commonly used in token-based authentication (e.g. JWT).
     *
     * @param int $id
     * @return AuthenticatableInterface|null
     */
    public function findById(int $id): ?AuthenticatableInterface;
    
    /**
     * Find a user by a unique identity value such as email, username, phone, etc.
     *
     * This method provides a flexible lookup mechanism for interactive login forms,
     * supporting various types of user identifiers.
     *
     * Example implementation:
     * if (str_contains($identity, '@')) {
     *     return $this->findByEmail($identity);
     * }
     * return $this->findByUsername($identity);
     *
     * @param string $identity
     * @return AuthenticatableInterface|null
     */
    public function findByIdentity(string $identity): ?AuthenticatableInterface;

}
/** End of UserRepositoryInterface **/