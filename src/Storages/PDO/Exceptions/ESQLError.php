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

namespace Scaleum\Storages\PDO\Exceptions;

use Scaleum\Stdlib\Exceptions\EDatabaseError;

/**
 * ESQLError
 *
 * @author Maxim Kirichenko <kirichenko.maxim@gmail.com>
 */
class ESQLError extends EDatabaseError {
    /**
     * @var string
     */
    private $sqlState;

    /**
     * To String prints both code and SQL state.
     *
     * @return string
     */
    public function __toString(): string {
        return '[' . $this->getSQLState() . '] - ' . $this->getMessage() . "\n" . $this->getTraceAsString();
    }

    /**
     * Returns an ANSI-92 compliant SQL state.
     *
     * @return string
     */
    public function getSQLState() {
        return $this->sqlState;
    }

    /**
     * Returns the raw SQL STATE, possibly compliant with
     * ANSI SQL error codes - but this depends on database driver.
     *
     * @param string $sqlState SQL state error code
     *
     * @return void
     */
    public function setSQLState($sqlState) {
        $this->sqlState = $sqlState;
    }
}
/** End of ESQLError **/