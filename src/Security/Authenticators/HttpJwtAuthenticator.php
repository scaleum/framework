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
use Scaleum\Security\Services\JwtManager;
use Scaleum\Security\Supports\TokenResolver;
use Scaleum\Stdlib\SAPI\Explorer;
use Scaleum\Stdlib\SAPI\SapiMode;

/**
 * JwtAuthenticator
 *
 * @author Maxim Kirichenko <kirichenko.maxim@gmail.com>
 */
class HttpJwtAuthenticator extends ReportableAbstract implements AuthenticatorInterface {

    public function __construct(
        private TokenResolver $tokenResolver,
        private JwtManager $jwtService,
        private UserRepositoryInterface $userRepository
    ) {}

    public function attempt(array $credentials, array $headers = []): ?AuthenticatableInterface {
        if (($mode = Explorer::getTypeFamily()) !== SapiMode::HTTP) {
            $this->addReport('debug', sprintf("Unsupported SAPI mode: `%s`",$mode->getName()),'INVALID_MODE');
            return null;
        }

        if (empty($headers)) {
            $headers = function_exists('getallheaders') ? getallheaders() : TokenResolver::fromServer($_SERVER);
        } elseif (TokenResolver::isServerHeaders($headers)) {
            $headers = TokenResolver::fromServer($headers);
        }

        $token = $credentials['token'] ?? $this->tokenResolver->resolve($_GET, $_POST, $headers, $_COOKIE);

        if (! $token) {
            $this->addReport('debug', 'Token credentials not found','INVALID_CREDENTIALS');
            return null;
        }

        $payload = $this->jwtService->verify($token);
        if ($payload && $payload->getUserId()) {
            return $this->userRepository->findById($payload->getUserId());
        } else {
            $this->addReport('error', $this->jwtService->getLastError() ?? 'Invalid token', 'INVALID_TOKEN');
        }

        return null;
    }

}
/** End of JwtAuthenticator **/