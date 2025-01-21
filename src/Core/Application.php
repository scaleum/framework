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

use Scaleum\Config\LoaderResolver;
use Scaleum\Core\KernelAbstract;
use Scaleum\Core\KernelEvents;
use Scaleum\Events\EventHandlerInterface;
use Scaleum\Events\EventManagerInterface;
use Scaleum\Services\ServiceProviderInterface;
use Scaleum\Stdlib\Exception\ERuntimeError;
use Scaleum\Stdlib\Helper\FileHelper;
use Scaleum\Stdlib\Helper\PathHelper;

/**
 * Application
 *
 * @author Maxim Kirichenko <kirichenko.maxim@gmail.com>
 */
class Application extends KernelAbstract {
    
    public function __construct(array $array = []) {
        parent::__construct($array);
    }
}
/** End of Application **/