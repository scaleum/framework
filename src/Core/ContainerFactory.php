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

use DI\ContainerBuilder;
use Psr\Container\ContainerInterface;
use Scaleum\Stdlib\Base\Hydrator;
use Scaleum\Stdlib\Helper\StringHelper;

/**
 * ContainerFactory
 *
 * @author Maxim Kirichenko <kirichenko.maxim@gmail.com>
 */
class ContainerFactory extends Hydrator {
    private ?ContainerBuilder $builder = null;
    protected array $definitions       = [];
    protected array $configurators     = [];

    public function __construct(array $config = []) {
        parent::__construct($config);
        $this->flush();
    }

    public function addDefinition(string $key, mixed $definition): self {
        $this->definitions[$key] = $definition;
        return $this;
    }

    public function addDefinitions(array $definitions): self {
        $this->definitions = array_merge_recursive($this->definitions, $definitions);
        return $this;
    }

    public function addConfigurator(ContainerConfiguratorInterface $configurator): self {
        $this->configurators[] = $configurator;
        return $this;
    }

    public function addConfigurators(array $configurators): self {
        foreach ($configurators as $configurator) {
            if (! $configurator instanceof ContainerConfiguratorInterface) {
                throw new \InvalidArgumentException(
                    sprintf(
                        'Configurator must be an instance of `ContainerConfiguratorInterface` given `%s`',
                        is_object($configurator) ? StringHelper::className($configurator,true) : gettype($configurator)
                    )
                );
            }

            $this->addConfigurator($configurator);
        }
        return $this;
    }

    public function build(): ContainerInterface {
        $builder = $this->getBuilder();
        foreach ($this->configurators as $configurator) {
            $configurator->configure($builder);
        }
        $builder->addDefinitions($this->definitions);
        $container = $builder->build();
        $this->flush();

        return $container;
    }

    public function flush(): void {
        $this->definitions   = [];
        $this->configurators = [];
        $this->builder       = new ContainerBuilder();
    }

    /**
     * Get the value of builder
     */
    public function getBuilder() {
        if ($this->builder === null) {
            $this->builder = new ContainerBuilder();
        }
        return $this->builder;
    }
}
/** End of ContainerFactory **/