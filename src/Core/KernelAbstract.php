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
use Scaleum\Stdlib\Exception\ERuntimeError;

/**
 * KernelAbstract
 *
 * @author Maxim Kirichenko <kirichenko.maxim@gmail.com>
 */
abstract class KernelAbstract {
    protected ?ContainerInterface $container = null;
    public function __construct(?ContainerInterface $container = null) {
        if ($container === null) {
            $factory = new ContainerFactory();
            $factory->addTuner(new KernelTuner());
            $container = $factory->build();
        }

        $this->container = $container;
    }

    /**
     * Get the value of container
     */
    public function getContainer(): ContainerInterface {
        if (!$this->container) {
            throw new ERuntimeError('Cannot retrieve the container from a kernel.');
        }
        return $this->container;
    }

    public function run(): void {
        var_export($this->container);
    }
}
/** End of KernelAbstract **/