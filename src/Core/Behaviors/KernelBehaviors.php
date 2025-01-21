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

namespace Scaleum\Core\Behaviors;

use Scaleum\Core\KernelEvents;
use Scaleum\Core\KernelProviderAbstract;
use Scaleum\Events\Event;
use Scaleum\Events\EventHandlerInterface;
use Scaleum\Events\EventManagerInterface;
use Scaleum\Services\ServiceProviderInterface;
use Scaleum\Services\ServiceRegistry;
use Scaleum\Stdlib\Exception\ERuntimeError;

/**
 * KernelBehavior
 *
 * @author Maxim Kirichenko <kirichenko.maxim@gmail.com>
 */
class KernelBehaviors extends KernelProviderAbstract implements EventHandlerInterface {
    public function register(EventManagerInterface $eventManager): void {;
        $eventManager->on(KernelEvents::BOOTSTRAP, [$this, 'onBootstrap'], -9999);
    }

    public function onBootstrap(Event $event): void {
        if (! ($provider = $this->getKernel()->get('service-manager')) instanceof ServiceProviderInterface) {
            throw new ERuntimeError('Service manager must implement ServiceProviderInterface');
        }
        ServiceRegistry::setProvider($provider);        
    }
}
/** End of KernelBehavior **/