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

use Psr\Container\ContainerInterface;
use Scaleum\Core\Contracts\HandlerInterface;
use Scaleum\Core\Contracts\ResponderInterface;
use Scaleum\Core\DependencyInjection\Framework;
use Scaleum\Events\EventManagerInterface;
use Scaleum\Routing\Route;
use Scaleum\Routing\Router;
use Scaleum\Stdlib\Exceptions\EHttpException;
use Scaleum\Stdlib\Exceptions\ENotFoundError;
use Scaleum\Stdlib\Exceptions\ERuntimeError;

/**
 * HttpHandler
 *
 * @author Maxim Kirichenko <kirichenko.maxim@gmail.com>
 */
class RequestHandler implements HandlerInterface {
    protected EventManagerInterface $events;

    public function __construct(protected ContainerInterface $container) {
        if (! ($events = $this->container->get(Framework::SVC_EVENTS)) instanceof EventManagerInterface) {
            throw new ERuntimeError("Event manager is not an instance of EventManagerInterface");
        }
        $this->events = $events;
    }

    public function handle(): ResponderInterface {
        try {
            /** @var Router $router */
            $router    = $this->container->get('router');
            $request   = InboundRequest::fromGlobals();
            $routeInfo = $router->match($request->getUri()->getPath(), $request->getMethod());

            $controller = (new ControllerResolver($this->container))->resolve($routeInfo);
            $this->events->dispatch(HandlerInterface::EVENT_GET_REQUEST, $this, ['request' => $request]);
            $response = (new ControllerInvoker())->invoke($controller, $routeInfo);
            $this->events->dispatch(HandlerInterface::EVENT_GET_RESPONSE, $this, ['response' => $response]);
            return $response;
        } catch (ENotFoundError $exception) {
            throw new EHttpException(404, $exception->getMessage(), $exception);
        } catch (\Throwable $exception) {
            throw new EHttpException(code: $exception->getCode(), message: $exception->getMessage(), previous: $exception);
        }
    }
}
/** End of HttpHandler **/