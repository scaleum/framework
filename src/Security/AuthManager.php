<?php
declare(strict_types=1);
/**
 * This file is part of Scaleum Framework.
 *
 * (C) 2009-2025 Maxim Kirichenko <kirichenko.maxim@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Scaleum\Security;

use Scaleum\Security\Contracts\AuthenticatableInterface;


/**
 * AuthManager
 *
 * @author Maxim Kirichenko <kirichenko.maxim@gmail.com>
 */
class AuthManager
{
    private array $strategies;

    public function __construct(array $strategies)
    {
        $this->strategies = $strategies;
    }

    public function authenticate(array $credentials, array $headers = []): ?AuthenticatableInterface
    {
        foreach ($this->strategies as $strategy) {
            $user = $strategy->attempt($credentials, $headers);
            if ($user !== null) {
                return $user;
            }
        }

        return null;
    }
}
/** End of AuthManager **/