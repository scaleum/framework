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

use Scaleum\Stdlib\Exception\ERuntimeError;

/**
 * KernelProviderAbstract
 *
 * @author Maxim Kirichenko <kirichenko.maxim@gmail.com>
 */
abstract class KernelProviderAbstract implements KernelProviderInterface {
    protected ?KernelInterface $kernel = null;

    public function __construct(?KernelInterface $kernel) {
        if (null !== $kernel) {
            $this->setKernel($kernel);
        }
    }

    public function getKernel(): KernelInterface {
        if (null === $this->kernel || ! $this->kernel instanceof KernelInterface) {
            throw new ERuntimeError('Kernel is not set');
        }
        return $this->kernel;
    }

    public function setKernel(KernelInterface $kernel): void {
        $this->kernel = $kernel;
    }
}
/** End of KernelProviderAbstract **/