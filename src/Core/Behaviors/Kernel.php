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
use Scaleum\Logger\LoggerGateway;
use Scaleum\Logger\LoggerProviderInterface;
use Scaleum\Services\ServiceLocator;
use Scaleum\Services\ServiceProviderInterface;
use Scaleum\Stdlib\Exceptions\ERuntimeError;
use Scaleum\Stdlib\Helpers\BytesHelper;

/**
 * KernelBehavior
 *
 * @author Maxim Kirichenko <kirichenko.maxim@gmail.com>
 */
class Kernel extends KernelProviderAbstract implements EventHandlerInterface {
    public function register(EventManagerInterface $events): void {
        $events->on(KernelEvents::BOOTSTRAP, [$this, 'onBootstrap'], -9999);
        $events->on("*", [$this, 'onEvent'], -9990);
    }

    public function onBootstrap(Event $event): void {
        # Accosiate logger manager with logger gateway
        if (! ($provider = $this->getKernel()->getContainer()->get('log.manager')) instanceof LoggerProviderInterface) {
            throw new ERuntimeError('Logger manager must implement LoggerProviderInterface');
        }
        LoggerGateway::setInstance($provider);

        # Accosiate service manager with service locator
        if (! ($provider = $this->getKernel()->getContainer()->get('service.manager')) instanceof ServiceProviderInterface) {
            throw new ERuntimeError('Service manager must implement ServiceProviderInterface');
        }
        ServiceLocator::setInstance($provider);
        // ServiceGateway::set('config', $this->getKernel()->getContainer()->get(Config::class));
    }

    public function onEvent(Event $event): void {
        switch ($event->getName()) {
        case KernelEvents::BOOTSTRAP:
            $this->debug('Application booting up ...');
            break;
        case KernelEvents::START:
            $this->debug('Application start');
            break;
        case KernelEvents::FINISH:
            $start = (float) $this->getKernel()->getContainer()->get('kernel.start');
            $end   = microtime(true);

            $this->debug(sprintf('Application finished, execution time: %s sec.', number_format($end - $start, 4)));
            $this->debug(sprintf('Application amount of memory allocated for PHP: %s kb.', BytesHelper::bytesTo(memory_get_usage(false))));
            $this->debug(sprintf('Application peak value of memory allocated by PHP: %s kb.', BytesHelper::bytesTo(memory_get_peak_usage(false))));
            break;
        default:
            $this->debug(sprintf('Event `%s` has been triggered', $event->getName()));
        }
    }
}
/** End of KernelBehavior **/