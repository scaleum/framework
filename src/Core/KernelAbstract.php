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
use Scaleum\DependencyInjection\Container;
use Scaleum\DependencyInjection\Contract\ConfiguratorInterface;
use Scaleum\Events\EventHandlerInterface;
use Scaleum\Events\EventManagerInterface;
use Scaleum\Services\ServiceProviderInterface;
use Scaleum\Stdlib\Base\Registry;
use Scaleum\Stdlib\Exception\ERuntimeError;
use Scaleum\Stdlib\Helper\EnvHelper;
use Scaleum\Stdlib\Helper\FileHelper;
use Scaleum\Stdlib\Helper\PathHelper;
use Scaleum\Stdlib\Helper\StringHelper;

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
        if (is_array($items = $this->getRegistry()->get('kernel.configs', []))) {
            foreach ($items as $filename) {
                if ($array = $this->getConfig($filename)) {
                    $this->getRegistry()->set('kernel.definitions', $array);
                }
            }
        }

        # add main container configurators
        $this->getRegistry()->set('kernel.configurators', [
            new DependencyInjection\Basic(),
            new DependencyInjection\Exceptions(),
            new DependencyInjection\Behaviors(),
        ]);

        # add main behaviors
        $this->getRegistry()->set('behaviors', [
            Behaviors\Kernel::class,
            Behaviors\Exceptions::class,
        ]);

        # init everything behaviors
        $array = $this->getRegistry()->get('behaviors');
        foreach ($array as $definition) {
            if ($handler = $this->getContainer()->get($definition)) {
                if (! $handler instanceof EventHandlerInterface) {
                    throw new ERuntimeError(
                        sprintf(
                            'Behavior must be an instance of `EventHandlerInterface` given `%s`',
                            is_object($handler) ? StringHelper::className($handler, true) : gettype($handler)
                        )
                    );
                }
                # TODO need to insert to wherever?
            }
        }

        # has predefined services?
        if (is_array($array = $this->getRegistry()->get('services'))) {
            foreach ($array as $key => $definition) {
                if ($service = $this->getContainer()->get($definition)) {
                    // TODO add check for ServiceInterface?
                    $this->getServiceManager()->setService($key, $service);
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
        if ($response = $this->getHandler()->handle()) {
            if (! $response instanceof ResponseInterface) {
                throw new ERuntimeError("Handler must return an instance of ResponseInterface");
            }
            $response->send();
        }
        $this->getEventManager()->dispatch(KernelEvents::FINISH);
    }

    public function halt($status, $message = null): void {

    }

    abstract public function getHandler(): HandlerInterface;

    /**
     * Retrieves the event manager instance.
     *
     * @return EventManagerInterface The event manager instance.
     */
    public function getEventManager(): EventManagerInterface {
        if (! ($result = $this->getContainer()->get('event.manager')) instanceof EventManagerInterface) {
            throw new \RuntimeException("Event manager is not an instance of EventManagerInterface");
        }
        return $result;
    }

    public function getServiceManager(): ServiceProviderInterface {
        if (! ($result = $this->getContainer()->get('service.manager')) instanceof ServiceProviderInterface) {
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

    public function getConfigDir(): string {
        return $this->getRegistry()->get('config_dir', realpath(PathHelper::join($this->getProjectDir(), 'config')));
    }

    public function getConfig(string $filename): array {
        # May be $filename is basename & need to be prepended with config_dir ?
        if (! file_exists($filename)) {
            $filename = FileHelper::prepFilename(PathHelper::join($this->getConfigDir(), $filename));
        }

        return (new LoaderResolver($this->getEnvironment()))->fromFile($filename);
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
     * Retrieves the dependency injection container.
     *
     */
    public function getContainer(): ContainerInterface {
        if (! $this->container instanceof ContainerInterface) {
            $this->container = $this->createContainer();
        }

        return $this->container;
    }

    protected function createContainer(): ContainerInterface {
        $container = new Container();
        foreach ($this->getRegistry()->get('kernel.configurators', []) as $configurator) {
            if (! $configurator instanceof ConfiguratorInterface) {
                throw new ERuntimeError(
                    sprintf(
                        'Configurator must be an instance of `ConfiguratorInterface` given `%s`',
                        is_object($configurator) ? StringHelper::className($configurator, true) : gettype($configurator)
                    )
                );
            }
            $configurator->configure($container);
        }

        foreach ($this->getRegistry()->get('kernel.definitions', []) as $key => $definition) {
            $container->addDefinition($key, $definition);
        }
        $container->addDefinition('environment', $this->getEnvironment());
        $container->addDefinition('kernel.project_dir', $this->getProjectDir());
        $container->addDefinition('kernel.config_dir', $this->getConfigDir());
        $container->addDefinition('kernel.start', microtime(true));
        $container->addDefinition('kernel', $this);

        return $container;
    }

}
/** End of KernelAbstract **/