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

namespace Scaleum\Core\Contracts;

use Scaleum\DependencyInjection\Contracts\ContainerProviderInterface;
use Scaleum\Events\EventManagerInterface;
use Scaleum\Services\ServiceProviderInterface;

/**
 * KernelInterface
 *
 * @author Maxim Kirichenko <kirichenko.maxim@gmail.com>
 */
interface KernelInterface extends ContainerProviderInterface {
    /**
     * Gets the project directory.
     *
     * @return string The project directory path.
     */
    public function getApplicationDir(): string;

    /**
     * Get the directory path of the configuration files.
     *
     * @return string The path to the configuration directory.
     */
    public function getConfigDir(): string;    

    /**
     * Returns the directory path where route definitions are stored.
     *
     * @return string The absolute or relative path to the routes directory.
     */
    public function getRouteDir():string;
    
    /**
     * Retrieves the current environment of the application.
     *
     * @return string The environment name.
     */
    public function getEnvironment(): string;

    /**
     * Bootstraps the kernel with the given configuration.
     *
     * @param array $config An optional array of configuration settings to initialize the kernel.
     * @return self Returns the current instance of the kernel for method chaining.
     */
    public function bootstrap(array $config = []): self;
    /**
     * Executes the kernel's main process.
     *
     * This method is responsible for running the core logic of the kernel.
     *
     * @return void
     */
    public function run(): void;
    /**
     * Halts the execution of the application with the given exit code.
     *
     * @param int $code The exit code to terminate the application with. Defaults to 0.
     *
     * @return void
     */
    public function halt(int $code = 0): void;
}
/** End of KernelInterface **/