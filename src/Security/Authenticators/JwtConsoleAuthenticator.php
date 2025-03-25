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
use Scaleum\Security\Services\JwtService;
use Scaleum\Stdlib\SAPI\Explorer;
use Scaleum\Stdlib\SAPI\SapiMode;

/**
 * JwtConsoleAuthenticator
 *
 * @author Maxim Kirichenko <kirichenko.maxim@gmail.com>
 */
class JwtConsoleAuthenticator implements AuthenticatorInterface {
    public function __construct(
        private JwtService $jwtService,
        private UserRepositoryInterface $userRepository
    ) {}

    public function attempt(array $credentials, array $headers = []): ?AuthenticatableInterface {
        if (Explorer::getTypeFamily() !== SapiMode::CONSOLE) {
            return null;
        }

        $token = $credentials['token'] ?? getenv('AUTH_TOKEN');
        if (! $token) {
            return null;
        }

        $payload = $this->jwtService->verify($token);
        if ($payload && $payload->getUserId()) {
            return $this->userRepository->findById($payload->getUserId());
        }

        return null;
    }
}
/** End of JwtConsoleAuthenticator **/