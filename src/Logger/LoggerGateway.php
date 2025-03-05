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
use Psr\Log\LogLevel;
use Scaleum\Stdlib\Exceptions\ERuntimeError;

/**
 * LoggerGateway - facade for logger provider
 *
 * @author Maxim Kirichenko <kirichenko.maxim@gmail.com>
 */
class LoggerGateway {
    protected static ?LoggerProviderInterface $instance = null;
    protected static bool $strictMode                   = true;

    public static function setProvider(LoggerProviderInterface $instance): void {
        self::$instance = $instance;
    }

    public static function getProvider(): ?LoggerProviderInterface {
        if (self::$instance === null && self::$strictMode) {
            throw new ERuntimeError(sprintf("Logger provider is not set in '%s'", __CLASS__));
        }
        return self::$instance;
    }

    public static function resetProvider(): void {
        self::$instance = null;
    }

    public static function strictModeOn(): void {
        self::$strictMode = true;
    }

    public static function strictModeOff(): void {
        self::$strictMode = false;
    }

    public static function getLogger(string $channel): ?LoggerInterface {
        return self::getProvider()?->getLogger($channel) ?? null;
    }

    public static function hasLogger(string $channel): bool {
        return self::getProvider()?->hasLogger($channel) ?? false;
    }

    public static function setLogger(string $channel, LoggerInterface $logger): void {
        self::getProvider()?->setLogger($channel, $logger);
    }

    public static function log($level, $message, array $context = []): void {
        self::getLogger($context['channel'] ?? 'default')?->log($level, $message, $context);
    }

    public static function emergency($message, array $context = []): void {
        self::log(LogLevel::EMERGENCY, $message, $context);
    }

    public static function alert($message, array $context = []): void {
        self::log(LogLevel::ALERT, $message, $context);
    }

    public static function critical($message, array $context = []): void {
        self::log(LogLevel::CRITICAL, $message, $context);
    }

    public static function error($message, array $context = []): void {
        self::log(LogLevel::ERROR, $message, $context);
    }

    public static function warning($message, array $context = []): void {
        self::log(LogLevel::WARNING, $message, $context);
    }

    public static function notice($message, array $context = []): void {
        self::log(LogLevel::NOTICE, $message, $context);
    }

    public static function info($message, array $context = []): void {
        self::log(LogLevel::INFO, $message, $context);
    }

    public static function debug($message, array $context = []): void {
        self::log(LogLevel::DEBUG, $message, $context);
    }
}
/** End of LoggerGateway **/