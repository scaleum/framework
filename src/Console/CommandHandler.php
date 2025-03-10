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

namespace Scaleum\Console;

use Psr\Container\ContainerInterface;
use Scaleum\Config\LoaderResolver;
use Scaleum\Core\Contracts\HandlerInterface;
use Scaleum\Core\Contracts\ResponderInterface;
use Scaleum\Events\EventManagerInterface;
use Scaleum\Stdlib\Exceptions\ERuntimeError;
use Scaleum\Stdlib\Helpers\ArrayHelper;

/**
 * CommandHandler
 *
 * @author Maxim Kirichenko <kirichenko.maxim@gmail.com>
 */
class CommandHandler implements HandlerInterface {
    protected EventManagerInterface $events;

    public function __construct(protected ContainerInterface $container) {
        if (! ($events = $this->container->get('event.manager')) instanceof EventManagerInterface) {
            throw new ERuntimeError("Event manager is not an instance of EventManagerInterface");
        }
        $this->events = $events;
    }
    public function handle(): ResponderInterface {
        /** @var  CommandDispatcher $dispatcher */
        $dispatcher = $this->container->get('commands.dispatcher');

        /** @var LoaderResolver $loader */
        $loader   = $this->container->get(LoaderResolver::class);
        $commands = [];
        if (file_exists($filename = $this->container->get('commands.file'))) {
            $commands = $loader->fromFile($filename);
        }

        if (is_dir($directory = $this->container->get('commands.directory'))) {
            $commands = ArrayHelper::merge($commands, $loader->fromDir($directory));
        }

        foreach ($commands as $name => $command) {
            $dispatcher->registerCommand($name, $this->container->get($command));
        }

        $request = new Request();
        $this->events->dispatch(HandlerInterface::EVENT_GET_REQUEST, $this, ['request' => $request]);
        $response = $dispatcher->dispatch($request);
        $this->events->dispatch(HandlerInterface::EVENT_GET_RESPONSE, $this, ['response' => $response]);

        return $response;
    }
}
/** End of CommandHandler **/