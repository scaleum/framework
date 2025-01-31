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

use Scaleum\Config\Config;
use Scaleum\Core\KernelEvents;
use Scaleum\Core\KernelProviderAbstract;
use Scaleum\Events\Event;
use Scaleum\Events\EventHandlerInterface;
use Scaleum\Events\EventManagerInterface;
use Scaleum\Logger\LoggerGateway;
use Scaleum\Logger\LoggerProviderInterface;
use Scaleum\Services\ServiceGateway;
use Scaleum\Services\ServiceProviderInterface;
use Scaleum\Stdlib\Exceptions\ERuntimeError;

/**
 * KernelBehavior
 *
 * @author Maxim Kirichenko <kirichenko.maxim@gmail.com>
 */
class Kernel extends KernelProviderAbstract implements EventHandlerInterface {
    public function register(EventManagerInterface $eventManager): void {
        $eventManager->on(KernelEvents::BOOTSTRAP, [$this, 'onBootstrap'], -9999);
    }

    public function onBootstrap(Event $event): void {
        # Accosiate service manager with service gateway
        if (! ($provider = $this->getKernel()->getContainer()->get('service.manager')) instanceof ServiceProviderInterface) {
            throw new ERuntimeError('Service manager must implement ServiceProviderInterface');
        }
        ServiceGateway::setProvider($provider);
        ServiceGateway::set('config', $this->getKernel()->getContainer()->get(Config::class));

        # Accosiate logger manager with logger gateway
        if (! ($provider = $this->getKernel()->getContainer()->get('log.manager')) instanceof LoggerProviderInterface) {
            throw new ERuntimeError('Logger manager must implement LoggerProviderInterface');
        }
        LoggerGateway::setProvider($provider);

//         if($config = $this->getKernel()->getContainer()->get(Config::class)) {
//             $config->set('var1',10);
//             var_export($config);
//         }
// usleep(1000);
//         if($config2 = $this->getKernel()->getContainer()->get(Config::class)) {
//             $config2->set('var2',20);
//             // var_dump($config2->get('var1'),$config2->get('var2'));
//             var_export($config2);
//         }

        // var_dump($this->getKernel()->getContainer());
        // echo 'App::onBoot';
    }
}
/** End of KernelBehavior **/