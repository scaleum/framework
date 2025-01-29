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

    // public function log($level, $message, array $context = []): void
    // {
    //     $channel = $context['channel'] ?? 'default';

    //     if (!isset($this->loggers[$channel])) {
    //         throw new InvalidArgumentException("Logger for channel '{$channel}' not found.");
    //     }

    //     $this->loggers[$channel]->log($level, $message, $context);
    // }

    // public function emergency($message, array $context = []): void
    // {
    //     $this->log(LogLevel::EMERGENCY, $message, $context);
    // }

    // public function alert($message, array $context = []): void
    // {
    //     $this->log(LogLevel::ALERT, $message, $context);
    // }

    // public function critical($message, array $context = []): void
    // {
    //     $this->log(LogLevel::CRITICAL, $message, $context);
    // }

    // public function error($message, array $context = []): void
    // {
    //     $this->log(LogLevel::ERROR, $message, $context);
    // }

    // public function warning($message, array $context = []): void
    // {
    //     $this->log(LogLevel::WARNING, $message, $context);
    // }

    // public function notice($message, array $context = []): void
    // {
    //     $this->log(LogLevel::NOTICE, $message, $context);
    // }

    // public function info($message, array $context = []): void
    // {
    //     $this->log(LogLevel::INFO, $message, $context);
    // }

    // public function debug($message, array $context = []): void
    // {
    //     $this->log(LogLevel::DEBUG, $message, $context);
    // }
}
