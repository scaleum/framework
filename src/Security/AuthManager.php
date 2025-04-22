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
class AuthManager
{
    private array $strategies;
    private array $reports = [];

    public function __construct(array $strategies)
    {
        $this->strategies = $strategies;
    }

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
                if ($entries) {
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

    public function getReports(): array
    {
        return $this->reports;
    }

    public function getErrors(): array
    {
        return array_filter($this->reports, fn($entry) => ($entry['type'] ?? null) === 'error');
    }

    public function hasErrors(): bool
    {
        return !empty($this->getErrors());
    }
}
/** End of AuthManager **/
