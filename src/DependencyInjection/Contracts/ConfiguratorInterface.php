<?php
declare(strict_types=1);
/**
 * This file is part of Scaleum Framework.
 *
 * (C) 2009-2025 Maxim Kirichenko <kirichenko.maxim@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Scaleum\DependencyInjection\Contracts;

use Scaleum\DependencyInjection\Container;

/**
 * ConfiguratorInterface
 *
 * @author Maxim Kirichenko <kirichenko.maxim@gmail.com>
 */
interface ConfiguratorInterface
{
    public function configure(Container $container): void;
}
/** End of ConfiguratorInterface **/