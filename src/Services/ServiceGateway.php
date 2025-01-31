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
 * ServiceGateway - facade for service provider
 * 
 * @author Maxim Kirichenko <kirichenko.maxim@gmail.com>
 */
class ServiceGateway {
    protected static ?ServiceProviderInterface $provider = null;

    public static function setProvider(ServiceProviderInterface $provider): void {
        self::$provider = $provider;
    }
    public static function getProvider(): ?ServiceProviderInterface {
        if (self::$provider === null) {
            throw new ERuntimeError('Service provider is not set');
        }
        return self::$provider;
    }

    public static function get(string $str, mixed $default = null): mixed {
        return self::getProvider()->getService($str, $default);
    }

    public static function getAll(): array {
        return self::getProvider()->getAll();
    }

    public static function has(string $str): bool {
        return self::getProvider()->hasService($str);
    }

    public static function set(string $str, mixed $definition, bool $override = false): mixed {
        return self::getProvider()->setService($str, $definition, $override);
    }
}
/** End of ServiceGateway **/