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

use Scaleum\Core\DependencyInjection\Framework;
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
    protected float $time_start, $time_end;
    public function register(EventManagerInterface $events): void {
        $events->on(KernelEvents::BOOTSTRAP, [$this, 'onBootstrap'], 0);
        $events->on(KernelEvents::FINISH, [$this, 'onFinish'], 9999);
        $events->on("*", [$this, 'onEvent'], 2);
    }

    public function onBootstrap(Event $event): void {
        # Accosiate logger manager with logger gateway
        if (! ($loggerManager = $this->getKernel()->getContainer()->get(Framework::SVC_LOGGERS)) instanceof LoggerProviderInterface) {
            throw new ERuntimeError('Logger manager must implement LoggerProviderInterface');
        }
        LoggerGateway::setProvider($loggerManager);

        # Accosiate service manager with service locator
        if (! ($services = $this->getKernel()->getContainer()->get(Framework::SVC_POOL)) instanceof ServiceProviderInterface) {
            throw new ERuntimeError('Service manager must implement ServiceProviderInterface');
        }
        $services->setService(Framework::SVC_LOGGERS, $loggerManager);
        $services->setService(Framework::SVC_EVENTS, $this->getKernel()->getContainer()->get(Framework::SVC_EVENTS));

        ServiceLocator::setProvider($services);
    }

    public function onEvent(Event $event): void {
        switch ($event->getName()) {
        case KernelEvents::BOOTSTRAP:
            $this->info('Application booting up ...');
            break;
        case KernelEvents::START:
            $this->info('Application start');
            $this->time_start = microtime(true);
            break;
        case KernelEvents::HALT:
            $this->info('Application halted');
            break;
        default:
            $this->debug(sprintf('Event `%s` has been dispatched', $event->getName()));
        }
    }

    public function onFinish(Event $event): void {
        $this->time_end = microtime(true);
        $this->info('Application finished, execution time: ' . number_format($this->time_end - $this->time_start, 4) . ' sec.');
        $this->debug('Application amount of memory allocated for PHP: ' . BytesHelper::bytesTo(memory_get_usage(false)) . ' kb.');
        $this->debug('Application peak value of memory allocated by PHP: ' . BytesHelper::bytesTo(memory_get_peak_usage(false)) . ' kb.');
    }
}
/** End of KernelBehavior **/