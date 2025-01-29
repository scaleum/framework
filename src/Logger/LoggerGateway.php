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

/**
 * LoggerGateway - facade for logger provider
 *
 * @author Maxim Kirichenko <kirichenko.maxim@gmail.com>
 */
class LoggerGateway {
    protected static ?LoggerProviderInterface $provider = null;

    public static function setProvider(LoggerProviderInterface $provider): void {
        self::$provider = $provider;
    }

    public static function getProvider(): ?LoggerProviderInterface {
        self::ensureProviderIsSet();
        return self::$provider;
    }

    public static function getLogger(string $channel): LoggerInterface {
        self::ensureProviderIsSet();
        return self::$provider->getLogger($channel);
    }

    public static function hasLogger(string $channel): bool {
        self::ensureProviderIsSet();
        return self::$provider->hasLogger($channel);
    }

    public function setLogger(string $channel, LoggerInterface $logger): void {
        self::ensureProviderIsSet();
        self::$provider->setLogger($channel, $logger);
    }    

    public static function log($level, $message, array $context = []): void {
        self::getLogger($context['channel'] ?? 'default')->log($level, $message, $context);
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

    protected static function ensureProviderIsSet(): void {
        if (self::$provider === null) {
            throw new \RuntimeException('Logger provider is not set');
        }
    }
}
/** End of LoggerGateway **/