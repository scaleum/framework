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

namespace Scaleum\Http;

use Scaleum\Config\LoadersResolver;
use Scaleum\Core\HandlerInterface;
use Scaleum\Core\KernelAbstract;
use Scaleum\Core\KernelEvents;
use Scaleum\Events\EventHandlerInterface;
use Scaleum\Events\EventManagerInterface;
use Scaleum\Services\ServiceProviderInterface;
use Scaleum\Stdlib\Exceptions\ERuntimeError;
use Scaleum\Stdlib\Helpers\FileHelper;
use Scaleum\Stdlib\Helpers\PathHelper;

/**
 * Application
 *
 * @author Maxim Kirichenko <kirichenko.maxim@gmail.com>
 */
class Application extends KernelAbstract {
    protected ?HandlerInterface $handler = null;

    public function __construct(array $array = []) {
        parent::__construct($array);
    }

    public function getHandler():HandlerInterface{
        if($this->handler === null){
            $this->handler = new RequestHandler(new Request());
        }
        return $this->handler;
    }
}
/** End of Application **/