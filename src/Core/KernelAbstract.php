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
use Scaleum\Core\Contracts\HandlerInterface;
use Scaleum\Core\Contracts\KernelInterface;
use Scaleum\Core\Contracts\ResponderInterface;
use Scaleum\Core\DependencyInjection\Framework;
use Scaleum\DependencyInjection\Factories\ContainerFactory;
use Scaleum\Events\EventHandlerInterface;
use Scaleum\Events\EventManagerInterface;
use Scaleum\Services\ServiceProviderInterface;
use Scaleum\Stdlib\Base\Registry;
use Scaleum\Stdlib\Exceptions\ERuntimeError;
use Scaleum\Stdlib\Helpers\EnvHelper;
use Scaleum\Stdlib\Helpers\FileHelper;
use Scaleum\Stdlib\Helpers\PathHelper;
use Scaleum\Stdlib\Helpers\StringHelper;

/**
 * KernelAbstract
 *
 * @author Maxim Kirichenko <kirichenko.maxim@gmail.com>
 */
abstract class KernelAbstract implements KernelInterface {
    protected ?ContainerInterface $container = null;
    protected ?Registry $registry            = null;
    protected bool $inReadiness              = false;
    protected bool $inStarted                = false;
    protected bool $inFinished               = false;
    protected bool $inHalting                = false;
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

        if (is_array($items = $this->getRegistry()->get('kernel.configs', []))) {
            foreach ($items as $filename) {
                if ($array = $this->getConfig($filename)) {
                    $this->getRegistry()->set('kernel.definitions', $array);
                }
            }
        }

        # add main container configurators
        $this->getRegistry()->set('kernel.configurators', [
            new DependencyInjection\Framework(),
            new DependencyInjection\ExceptionHandling(),
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
            // var_export($definition);
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
        $this->inReadiness = true;
        return $this;
    }

    public function run(): void {
        if (! $this->inReadiness) {
            $this->bootstrap();
            $this->inReadiness = true;
        }

        $this->dispatchStart();

        if ($response = $this->getHandler()->handle()) {
            if (! $response instanceof ResponderInterface) {
                throw new ERuntimeError("Handler must return an instance of `ResponderInterface`");
            }
            $response->send();
        }

        $this->dispatchFinish();
        $this->halt(0);
    }

    public function halt(int $code = 0): void {
        if (! $this->inHalting) {
            $this->inHalting = true;
            $this->dispatchFinish();
        }

        $this->getEventManager()->dispatch(KernelEvents::HALT, null, [
            'code' => $code,
        ]);
        exit($code);
    }

    public function isStarted(): bool {
        return $this->inStarted;
    }

    public function isFinished(): bool {
        return $this->inFinished;
    }

    protected function dispatchStart(): void {
        if ($this->inStarted) {
            return;
        }

        $this->getEventManager()->dispatch(KernelEvents::START);
        $this->inStarted = true;
    }

    protected function dispatchFinish(): void {
        if ($this->inFinished) {
            return;
        }

        $this->getEventManager()->dispatch(KernelEvents::FINISH);
        $this->inFinished = true;
    }

    abstract public function getHandler(): HandlerInterface;

    /**
     * Retrieves the event manager instance.
     *
     * @return EventManagerInterface The event manager instance.
     */
    public function getEventManager(): EventManagerInterface {
        if (! ($result = $this->getContainer()->get(Framework::SVC_EVENTS)) instanceof EventManagerInterface) {
            throw new \RuntimeException("Event manager is not an instance of EventManagerInterface");
        }
        return $result;
    }

    public function getServiceManager(): ServiceProviderInterface {
        if (! ($result = $this->getContainer()->get(Framework::SVC_POOL)) instanceof ServiceProviderInterface) {
            throw new ERuntimeError("Service manager is not an instance of ServiceProviderInterface");
        }
        return $result;
    }

    public function getApplicationDir(): string {
        $path = PathHelper::getScriptDir();
        return $this->getRegistry()->get('application_dir', realpath($path) ?: $path);
    }

    public function getConfigDir(): string {
        $path = PathHelper::join($this->getApplicationDir(), 'config');
        return $this->getRegistry()->get('config_dir', realpath($path) ?: $path);
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
            ContainerFactory::reset();
            ContainerFactory::addConfigurators($this->getRegistry()->get('kernel.configurators', []));
            ContainerFactory::addDefinitions($this->getRegistry()->get('kernel.definitions', []));
            ContainerFactory::addDefinitions([
                'environment'            => $this->getEnvironment(),
                'kernel.application_dir' => $this->getApplicationDir(),
                'kernel.config_dir'      => $this->getConfigDir(),
                'kernel'                 => $this,
            ]);

            $this->container = ContainerFactory::create();
        }

        return $this->container;
    }
}
/** End of KernelAbstract **/