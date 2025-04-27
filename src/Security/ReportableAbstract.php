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

namespace Scaleum\Security;

use Scaleum\Security\Contracts\ReportableAuthenticatorInterface;

/**
 * ReportableAbstract
 *
 * @author Maxim Kirichenko <kirichenko.maxim@gmail.com>
 */
abstract class ReportableAbstract implements ReportableAuthenticatorInterface {
    protected array $reports = [];

    public function getReports(): ?array {
        return $this->reports;
    }

    public function addReport(string $type, string $message, ?string $code = null): void {
        $this->reports[] = compact('type', 'message', 'code');
    }

    public function clearReports(): void {
        $this->reports = [];
    }
}
/** End of ReportableAbstract **/