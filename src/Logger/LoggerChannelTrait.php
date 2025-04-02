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

namespace Scaleum\Logger;

use Psr\Log\LoggerInterface;
use Psr\Log\LoggerTrait;
use Psr\Log\LogLevel;

/**
 * LoggerChannelTrait
 *
 * @author Maxim Kirichenko <kirichenko.maxim@gmail.com>
 */
trait LoggerChannelTrait {
    use LoggerTrait;
    abstract public function getLoggerChannel(): string;
    public function getLogger(): ?LoggerInterface {
        return LoggerGateway::getLogger($this->getLoggerChannel());
    }
    public function log($level, $message, array $context = []): void {
        if (LoggerGateway::hasLogger($channel = $this->getLoggerChannel())) {
            LoggerGateway::getLogger($channel)->log($level, $message, $context);
        }
    }
}
/** End of LoggerChannelTrait **/