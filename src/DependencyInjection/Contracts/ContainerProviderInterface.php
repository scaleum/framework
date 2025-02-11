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

namespace Scaleum\DependencyInjection\Contracts;
use Psr\Container\ContainerInterface;

/**
 * ContainerProviderInterface
 *
 * @author Maxim Kirichenko <kirichenko.maxim@gmail.com>
 */
interface ContainerProviderInterface {
    /**
     * Retrieves the service container.
     *
     * @return ContainerInterface The service container instance.
     */
    public function getContainer(): ContainerInterface;
}
/** End of ContainerProviderInterface **/