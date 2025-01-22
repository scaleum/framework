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
use Scaleum\Config\LoaderResolver;
use Scaleum\Events\EventHandlerInterface;
use Scaleum\Events\EventManagerInterface;
use Scaleum\Services\ServiceProviderInterface;
use Scaleum\Stdlib\Base\Registry;
use Scaleum\Stdlib\Exception\ERuntimeError;
use Scaleum\Stdlib\Helper\EnvHelper;
use Scaleum\Stdlib\Helper\FileHelper;
use Scaleum\Stdlib\Helper\PathHelper;

/**
 * KernelAbstract
 *
 * @author Maxim Kirichenko <kirichenko.maxim@gmail.com>
 */
abstract class KernelAbstract implements KernelInterface {
    protected ?ContainerInterface $container = null;
    protected ?Registry $registry            = null;
    protected bool $in_readiness             = false;
    /**
     * Constructor for the KernelAbstract class.
     *
     * @param array $array Optional array parameter to initialize the object.
     */
    public function __construct(array $array = []) {
        $this->setRegistry(new Registry($array, '.'));
    }

    public function bootstrap(array $config = []): self {
        $this->getRegistry()->merge($config);

        # configs
        $configs = $this->getRegistry()->get('kernel.configs', []);
        if (is_array($configs)) {
            foreach ($configs as $config) {
                // full path to config file?
                if (! file_exists($filename = $config)) {
                    $filename = FileHelper::prepFilename(PathHelper::join($this->getProjectDir(), "config", $config));
                }
                // load config file
                if (file_exists($filename) && is_readable($filename)) {
                    if ($array = (new LoaderResolver($this->getEnvironment()))->fromFile($filename)) {
                        $this->getRegistry()->set('kernel.definitions', $array);
                    }
                }
            }
        }

        # self container`s configurators
        $this->getRegistry()->set('kernel.configurators', [
            new DependencyInjection\Basic(),
            new DependencyInjection\Exceptions(),
            new DependencyInjection\Behaviors(),
        ]);

        # init behaviors
        if (is_array($array = $this->get('behaviors'))) {
            foreach ($array as $definition) {
                if ($subscriber = $this->get($definition)) {
                    if (! $subscriber instanceof EventHandlerInterface) {
                        throw new ERuntimeError("Subscriber `{$definition}` is not an instance of EventHandlerInterface");
                    }
                }
            }
        }

        # init services
        if (is_array($array = $this->get('services'))) {
            foreach ($array as $definition) {
                if ($service = $this->get($definition)) {
                    $this->getServiceManager()->set($definition, $service);
                }
            }
        }

        # done
        $this->getEventManager()->dispatch(KernelEvents::BOOTSTRAP);
        $this->in_readiness = true;
        return $this;
    }

    public function run(): void {
        if (! $this->in_readiness) {
            $this->bootstrap();
            $this->in_readiness = true;
        }

        $this->getEventManager()->dispatch(KernelEvents::START);
        // if($response = $this->getHandler()->handle()) {
        //     if(! $response instanceof ResponseInterface) {
        //         throw new ERuntimeError("Handler must return an instance of ResponseInterface");
        //     }
        //     $response->send();
        // }
        $this->getEventManager()->dispatch(KernelEvents::FINISH);
    }

    public function halt($status, $message = null): void {

    }

    // abstract public function getHandler():HandlerInterface;

    /**
     * Retrieves the event manager instance.
     *
     * @return EventManagerInterface The event manager instance.
     */
    public function getEventManager(): EventManagerInterface {
        if (! ($result = $this->get('event-manager')) instanceof EventManagerInterface) {
            throw new \RuntimeException("Event manager is not an instance of EventManagerInterface");
        }
        return $result;
    }

    public function getServiceManager(): ServiceProviderInterface {
        if (! ($result = $this->get('service-manager')) instanceof ServiceProviderInterface) {
            throw new ERuntimeError("Service manager is not an instance of ServiceProviderInterface");
        }
        return $result;
    }

    /**
     * Get the value of project_dir
     */
    public function getProjectDir(): string {
        return $this->getRegistry()->get('project_dir', realpath(PathHelper::getScriptDir()));
    }

    /**
     * Get the value of environment
     */
    public function getEnvironment(): string {
        return $this->getRegistry()->get('environment', EnvHelper::get('APP_ENV', ''));
    }

    /**
     * Retrieves the registry instance.
     *
     * @return Registry The registry instance.
     */
    public function getRegistry() {
        return $this->registry;
    }

    /**
     * Sets the registry instance.
     *
     * @param Registry $registry The registry instance to set.
     * @return self Returns the current instance for method chaining.
     */
    public function setRegistry(Registry $registry): self {
        $this->registry = $registry;
        return $this;
    }

    /**
     * Retrieves an entry from the kernel container.
     *
     * @param string $string The key of the entry to retrieve.
     * @param mixed $default The default value to return if the entry does not exist. Default is null.
     * @return mixed The value of the entry, or the default value if the entry does not exist.
     */
    public function get(string $string, mixed $default = null): mixed {
        if ($this->getContainer()->has($string)) {
            return $this->getContainer()->get($string);
        }
        return $default;
    }

    /**
     * Retrieves the dependency injection container.
     *
     * @return ContainerInterface The dependency injection container.
     */
    public function getContainer(): ContainerInterface {
        if (! $this->container instanceof ContainerInterface) {
            $this->container = $this->createContainer();
        }

        return $this->container;
    }

    protected function createContainer(): ContainerInterface {
        return (new ContainerFactory())
            ->addConfigurators($this->getRegistry()->get('kernel.configurators', []))
            ->addDefinitions($this->getRegistry()->get('kernel.definitions', []))
            ->addDefinitions([
                'environment'        => $this->getEnvironment(),
                'kernel.project_dir' => $this->getProjectDir(),
                'kernel.start'       => microtime(true),
                'kernel'             => $this,
            ])
            ->build();
    }

}
/** End of KernelAbstract **/