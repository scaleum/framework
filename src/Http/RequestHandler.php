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
use Psr\Http\Message\ResponseInterface;
use Scaleum\Core\Contracts\HandlerInterface;
use Scaleum\Events\EventManagerInterface;
use Scaleum\Routing\Router;
use Scaleum\Stdlib\Exceptions\EHttpException;
use Scaleum\Stdlib\Exceptions\ENotFoundError;

/**
 * HttpHandler
 *
 * @author Maxim Kirichenko <kirichenko.maxim@gmail.com>
 */
class RequestHandler implements HandlerInterface {
    public const EVENT_GET_REQUEST  = 'handle::request';
    public const EVENT_GET_RESPONSE = 'handle::response';
    protected EventManagerInterface $events;

    public function __construct(protected ContainerInterface $container) {
        if (! ($events = $this->container->get('event.manager')) instanceof EventManagerInterface) {
            throw new \RuntimeException("Event manager is not an instance of EventManagerInterface");
        }
        $this->events = $events;
    }

    public function handle(): ResponseInterface {
        try {
            /** @var Router $router */
            $router  = $this->container->get('router');

            $request = Request::fromGlobals();
            $this->events->dispatch(self::EVENT_GET_REQUEST, $this, ['request' => $request]);
            $routeInfo  = $router->match($request->getUri()->getPath(), $request->getMethod());

            $controller = (new ControllerResolver($this->container))->resolve($routeInfo);
            $response   = (new ControllerInvoker())->invoke($controller, $routeInfo);

            $this->events->dispatch(self::EVENT_GET_RESPONSE, $this, ['response' => $response]);
            return $response;
        } catch (ENotFoundError $exception) {
            throw new EHttpException(404, $exception->getMessage(), $exception);
        } catch (\Throwable $exception) {
            throw new EHttpException(message: $exception->getMessage(), previous: $exception);
        }
    }
}
/** End of HttpHandler **/