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

namespace Scaleum\Security\Contracts;


/**
 * ReportableAuthenticatorInterface
 *
 * @author Maxim Kirichenko <kirichenko.maxim@gmail.com>
 */
interface ReportableAuthenticatorInterface
{
    /**
     * Returns an array of reports from the authentication strategy
     * @return array[]|null
     */
    public function getReports(): ?array;

    /**
     * Add a line to the report
     * @param string $type    Type of report: 'error', 'debug', 'info'
     * @param string $message Text of the report
     * @param string|null $code Code of the report (optional)
     */
    public function addReport(string $type, string $message, ?string $code = null): void;

    /**
     * Clears the current reports
     */
    public function clearReports(): void;
}
/** End of ReportableAuthenticatorInterface **/
