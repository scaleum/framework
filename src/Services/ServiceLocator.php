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

    public static function setInstance(ServiceProviderInterface $instance): void {
        self::$instance = $instance;
    }
    public static function getInstance(): ?ServiceProviderInterface {
        if (self::$instance === null) {
            throw new ERuntimeError('Service provider is not set');
        }
        return self::$instance;
    }

    public static function get(string $str, mixed $default = null): mixed {
        return self::getInstance()->getService($str, $default);
    }

    public static function getAll(): array {
        return self::getInstance()->getAll();
    }

    public static function has(string $str): bool {
        return self::getInstance()->hasService($str);
    }

    public static function set(string $str, mixed $definition, bool $override = false): mixed {
        return self::getInstance()->setService($str, $definition, $override);
    }
}
/** End of ServiceLocator **/