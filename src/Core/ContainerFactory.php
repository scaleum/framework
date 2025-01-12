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
use DI\ContainerBuilder;

/**
 * ContainerFactory
 *
 * @author Maxim Kirichenko <kirichenko.maxim@gmail.com>
 */
class ContainerFactory {
    private array $definitions = [];
    private array $tuners      = [];

    public function addDefinition(string $id, mixed $definition): void {
        $this->definitions[$id] = $definition;
    }

    public function addDefinitions(array $definitions): void {
        $this->definitions = array_merge($this->definitions, $definitions);
    }

    public function addTuner(ContainerTunerInterface $tuner): void {
        $this->tuners[] = $tuner;
    }

    public function build(): ContainerInterface {
        $builder = new ContainerBuilder();

        foreach ($this->tuners as $tuner) {
            $tuner->handle($builder);
        }

        $builder->addDefinitions($this->definitions);

        return $builder->build();
    }
}
/** End of ContainerFactory **/