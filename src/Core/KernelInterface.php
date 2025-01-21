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

namespace Scaleum\Core;

use Psr\Container\ContainerInterface;

/**
 * KernelInterface
 *
 * @author Maxim Kirichenko <kirichenko.maxim@gmail.com>
 */
interface KernelInterface {
    /**
     * Gets the project directory.
     *
     * @return string The project directory path.
     */
    public function getProjectDir(): string;
    /**
     * Retrieves the current environment of the application.
     *
     * @return string The environment name.
     */
    public function getEnvironment(): string;

    /**
     * Retrieves the service container.
     *
     * @return ContainerInterface The service container instance.
     */
    public function getContainer(): ContainerInterface;

    /**
     * Retrieves an entry from the kernel container.
     *
     * @param string $string The key or identifier for the entry.
     * @param mixed $default The default value to return if the entry is not found. Defaults to null.
     * @return mixed The value of the entry, or the default value if the entry is not found.
     */
    public function get(string $string, mixed $default = null): mixed;

    public function bootstrap(array $config = []): self;
    public function run(): void;
    public function halt($status, $message = null): void;
}
/** End of KernelInterface **/