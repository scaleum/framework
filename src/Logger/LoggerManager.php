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

namespace Scaleum\Logger;

use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Psr\Log\InvalidArgumentException;

class LoggerManager implements LoggerProviderInterface
{
    private array $loggers = [];

    public function getLogger(string $channel): LoggerInterface
    {
        if (!isset($this->loggers[$channel])) {
            throw new InvalidArgumentException("Logger for channel '{$channel}' not found.");
        }
        return $this->loggers[$channel];
    }

    public function hasLogger(string $channel): bool{
        return isset($this->loggers[$channel]);
    }

    public function setLogger(string $channel, LoggerInterface $logger): void
    {
        if ($this->hasLogger($channel)) {
            // shutdown the old logger
            if (method_exists($this->loggers[$channel], 'shutdown')) {
                $this->loggers[$channel]->shutdown();
            }
        }        
        $this->loggers[$channel] = $logger;
    }
}
