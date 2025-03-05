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

namespace Scaleum\Services;

use Scaleum\Stdlib\Exceptions\ERuntimeError;

/**
 * ServiceLocator - facade for service provider
 *
 * @author Maxim Kirichenko <kirichenko.maxim@gmail.com>
 */
class ServiceLocator {
    protected static ?ServiceProviderInterface $instance = null;
    protected static bool $strictMode                    = true;

    public static function setProvider(ServiceProviderInterface $instance): void {
        self::$instance = $instance;
    }
    public static function getProvider(): ?ServiceProviderInterface {
        if (self::$instance === null && self::$strictMode) {
            throw new ERuntimeError(sprintf("Service provider is not set in '%s'", __CLASS__));
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

    public static function get(string $str, mixed $default = null): mixed {
        return self::getProvider()?->getService($str, $default) ?? $default;
    }

    public static function getAll(): array {
        return self::getProvider()?->getAll() ?? [];
    }

    public static function has(string $str): bool {
        return self::getProvider()?->hasService($str) ?? false;
    }

    public static function set(string $str, mixed $definition, bool $override = false): mixed {
        return self::getProvider()?->setService($str, $definition, $override);
    }
}
/** End of ServiceLocator **/