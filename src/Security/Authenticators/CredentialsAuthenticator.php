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
use Scaleum\Security\Contracts\HasPasswordInterface;
use Scaleum\Security\Contracts\UserRepositoryInterface;
use Scaleum\Security\ReportableAbstract;

/**
 * CredentialsAuthenticator
 *
 * @author Maxim Kirichenko <kirichenko.maxim@gmail.com>
 */
class CredentialsAuthenticator extends ReportableAbstract implements AuthenticatorInterface {
    public function __construct(
        private UserRepositoryInterface $userRepository
    ) {}

    public function attempt(array $credentials, array $headers = []): ?AuthenticatableInterface {
        $identity = $credentials['identity'] ?? null;
        $password = $credentials['password'] ?? null;

        if (! $identity || ! $password) {
            return null;
        }

        $user = $this->userRepository->findByIdentity($identity);

        if ($user) {
            if ($this->verifyPassword($password, $user)) {
                return $user;
            } else {
                $this->addReport('error', 'Invalid password', 'INVALID_PASSWORD');
            }
        } else {
            $this->addReport('error', 'Identity not found or invalid', 'INVALID_CREDENTIALS');
        }

        return null;
    }

    private function verifyPassword(string $password, AuthenticatableInterface $user): bool {
        if (! $user instanceof HasPasswordInterface) {
            return false;
        }

        return password_verify($password, $user->getPasswordHash());
    }
}

/** End of CredentialsAuthenticator **/