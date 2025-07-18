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

namespace Scaleum\Security\Authenticators;

use Scaleum\Security\Contracts\AuthenticatableInterface;
use Scaleum\Security\Contracts\AuthenticatorInterface;
use Scaleum\Security\Contracts\UserRepositoryInterface;
use Scaleum\Security\ReportableAbstract;
use Scaleum\Stdlib\SAPI\Explorer;
use Scaleum\Stdlib\SAPI\SapiMode;

/**
 * UserConsoleAuthenticator
 *
 * @author Maxim Kirichenko <kirichenko.maxim@gmail.com>
 */
class ConsoleUserIdAuthenticator extends ReportableAbstract implements AuthenticatorInterface {
    public function __construct(private UserRepositoryInterface $userRepository) {}

    public function attempt(array $credentials, array $headers = []): ?AuthenticatableInterface {
        if (($mode = Explorer::getTypeFamily()) !== SapiMode::CONSOLE) {
            $this->addReport('debug', sprintf("Unsupported SAPI mode: `%s`",$mode->getName()),'INVALID_MODE');
            return null;
        }

        $userId = $credentials['user_id'] ?? getenv('AUTH_USER_ID');
        if (! $userId) {
            $this->addReport('debug', 'User credentials not found','INVALID_CREDENTIALS');
            return null;
        }

        return $this->userRepository->findById((int) $userId);
    }
}
/** End of UserConsoleAuthenticator **/