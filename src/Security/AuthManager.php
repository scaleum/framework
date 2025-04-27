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
use Scaleum\Security\Contracts\ReportableAuthenticatorInterface;
use Scaleum\Stdlib\Helpers\StringHelper;

/**
 * AuthManager
 *
 * @author Maxim Kirichenko <kirichenko.maxim@gmail.com>
 */
class AuthManager extends ReportableAbstract
{
    private array $strategies;
    public function __construct(array $strategies)
    {
        $this->strategies = $strategies;
    }

    /**
     * Authenticates a user based on the provided credentials and headers.
     *
     * @param array $credentials An associative array containing the user's credentials.
     * @param array $headers Optional headers to include during the authentication process.
     * @param bool $verbose Whether to enable verbose logging during authentication.
     * @return AuthenticatableInterface|null Returns an instance of AuthenticatableInterface if authentication is successful, or null if it fails.
     */
    public function authenticate(array $credentials, array $headers = [], bool $verbose = false): ?AuthenticatableInterface
    {
        $this->reports = [];
        foreach ($this->strategies as $strategy) {
            if ($strategy instanceof ReportableAuthenticatorInterface) {
                $strategy->clearReports();
            }

            $user = $strategy->attempt($credentials, $headers);

            if ($verbose && $strategy instanceof ReportableAuthenticatorInterface) {                
                $entries = $strategy->getReports();
                if ($entries !== null) {
                    foreach ($entries as $entry) {
                        $this->reports[] = array_merge(
                            ['strategy' => StringHelper::className($strategy, true)],
                            $entry
                        );
                    }
                }
            }

            if ($user !== null) {
                return $user;
            }
        }

        return null;
    }

    /**
     * Retrieves reports based on the specified type.
     *
     * @param string $type The type of reports to retrieve. Defaults to 'debug'.
     *                      Possible values may include 'debug', 'info', 'error', etc.
     * @return array An array of reports corresponding to the specified type.
     */
    public function getReportsByType(string $type = 'debug'): array {
        return array_values(array_filter($this->getReports(), fn($entry) => ($entry['type'] ?? null) === $type));
    }

    /**
     * Checks if there are reports of a specific type.
     *
     * @param string $type The type of reports to check for. Defaults to 'debug'.
     * @return bool True if reports of the specified type exist, false otherwise.
     */
    public function hasReports(string $type = 'debug'): bool {
        return ! empty($this->getReportsByType($type));
    }    
}
/** End of AuthManager **/
