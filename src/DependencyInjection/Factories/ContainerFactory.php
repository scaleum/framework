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

namespace Scaleum\DependencyInjection\Factories;

use Scaleum\DependencyInjection\Container;
use Scaleum\DependencyInjection\Contracts\ConfiguratorInterface;
use Scaleum\Stdlib\Exceptions\ERuntimeError;
use Scaleum\Stdlib\Helpers\StringHelper;

/**
 * ContainerFactory
 *
 * @author Maxim Kirichenko <kirichenko.maxim@gmail.com>
 */
class ContainerFactory {
    private static array $definitions = [];
    private static array $instances   = [];

    public static function addDefinition(string $id, mixed $value, bool $singleton = true): void {
        static::$definitions[$id] = [
            'value'     => $value,
            'singleton' => $singleton,
        ];
    }

    public static function addDefinitions(array $definitions, bool $singleton = true): void {
        foreach ($definitions as $id => $value) {
            static::addDefinition($id, $value, $singleton);
        }
    }

    public static function getDefinitions(): array {
        return static::$definitions;
    }

    public static function addConfigurator(ConfiguratorInterface $configurator): void {
        static::$instances[] = $configurator;
    }

    public static function addConfigurators(array $configurators): void {
        foreach ($configurators as $configurator) {
            if (! ($configurator instanceof ConfiguratorInterface)) {
                throw new ERuntimeError(
                    sprintf(
                        'Configurator must be an instance of `ConfiguratorInterface` given `%s`',
                        is_object($configurator) ? StringHelper::className($configurator, true) : gettype($configurator)
                    )
                );
            }
            static::addConfigurator($configurator);
        }
    }

    public static function getConfigurators(): array {
        return static::$instances;
    }

    public static function create(): Container {
        $container = new Container();
        // Configurators are executed in the order they were added
        foreach (static::getConfigurators() as $configurator) {
            $configurator->configure($container);
        }

        // Configuration definitions take precedence over configurators
        foreach (static::getDefinitions() as $id => $definition) {
            $container->addDefinition($id, $definition['value'], $definition['singleton']);
        }        
        static::reset();

        return $container;
    }

    public static function reset(): void {
        static::$definitions = [];
        static::$instances   = [];
    }
}
/** End of ContainerFactory **/