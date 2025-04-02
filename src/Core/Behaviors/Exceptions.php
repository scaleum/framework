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
use Scaleum\Stdlib\Exceptions\ExceptionHandlerInterface;

/**
 * Exceptions
 *
 * @author Maxim Kirichenko <kirichenko.maxim@gmail.com>
 */
class Exceptions extends KernelProviderAbstract implements EventHandlerInterface {
    protected ?ExceptionHandlerInterface $handler = null;

    public function register(EventManagerInterface $events): void {
        $events->on(KernelEvents::BOOTSTRAP, [$this, 'onBootstrap'], 0);
    }

    public function onBootstrap(Event $event): void {
        if ($handler = $this->getKernel()->getContainer()->get(ExceptionHandlerInterface::class)) {
            if ($handler instanceof ExceptionHandlerInterface) {
                $this->handler = $handler;
                set_error_handler([$this, 'handlerError']);
                set_exception_handler([$this, 'handlerException']);
                register_shutdown_function([$this, 'handlerShutdown']);
            } else {
                throw new \RuntimeException('Exception handler must implement ExceptionHandlerInterface');
            }
        }
    }

    public function handlerError(
        int $errno,
        string $errstr,
        ?string $errfile = null,
        ?int $errline = null,
    ): void {
        if (error_reporting() & $errno) {
            throw new \ErrorException($errstr, 0, $errno, $errfile, $errline);
        }
    }

    public function handlerException(
        \Throwable $exception
    ): void {
        try {
            $this->error($exception->getMessage(), ['exception' => $exception]);
            $this->getHandler()->handle($exception);
        } catch (\Throwable $e) {
            error_log($e->getMessage());
        }
    
        $this->getKernel()->halt(1);
    }

    public function handlerShutdown(): void {

        // Check for unhandled errors (fatal shutdown)
        $err = error_get_last();

        // If none, check function args (error handler)
        if ($err === null) {
            $err = func_get_args();
        }

        if (empty($err)) {
            return;
        }

        $err       = array_combine(['errno', 'errstr', 'errfile', 'errline'], $err);
        $exception = new \ErrorException($err['errstr'], 0, $err['errno'], $err['errfile'], $err['errline']);

        if (error_reporting() & $err['type']) {
            try {
                $this->emergency($exception->getMessage(), ['exception' => $exception]);
                $this->getHandler()->handle($exception);
                $this->getKernel()->halt(1);
                exit(1);
            } catch (\Throwable $e) {
                error_log($e->getMessage());
            }            
        }
    }

    /**
     * Get the value of handler
     */
    public function getHandler() {
        if (null === $this->handler) {
            throw new \RuntimeException('Exception handler is not set');
        }
        return $this->handler;
    }
}
/** End of Exceptions **/