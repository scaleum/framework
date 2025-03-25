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

namespace Scaleum\Auth\Authenticators;

use Scaleum\Auth\Contracts\AuthenticatableInterface;
use Scaleum\Auth\Contracts\AuthenticatorInterface;
use Scaleum\Auth\Contracts\UserRepositoryInterface;
use Scaleum\Stdlib\SAPI\Explorer;
use Scaleum\Stdlib\SAPI\SapiMode;
/**
 * UserConsoleAuthenticator
 *
 * @author Maxim Kirichenko <kirichenko.maxim@gmail.com>
 */
class UserConsoleAuthenticator implements AuthenticatorInterface
{
    public function __construct(private UserRepositoryInterface $userRepository) {}

    public function attempt(array $credentials, array $headers = []): ?AuthenticatableInterface
    {
        if(Explorer::getTypeFamily() !== SapiMode::CONSOLE) {
            return null;
        }

        $userId = $credentials['user_id'] ?? getenv('AUTH_USER_ID');
        if (!$userId) {
            return null;
        }

        return $this->userRepository->findById((int) $userId);
    }
}
/** End of UserConsoleAuthenticator **/