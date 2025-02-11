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
use Scaleum\Routing\Router;
use Scaleum\Stdlib\Exceptions\EHttpException;
use Scaleum\Stdlib\Exceptions\ENotFoundError;

/**
 * HttpHandler
 *
 * @author Maxim Kirichenko <kirichenko.maxim@gmail.com>
 */
class RequestHandler implements HandlerInterface {
    public function __construct(protected ContainerInterface $container) {}
    public function handle(): ResponseInterface {
        try {
            /** @var Router $router */
            $router     = $this->container->get('router');
            $request    = Request::fromGlobals();
            $routeInfo  = $router->match($request->getUri()->getPath(), $request->getMethod());
            $controller = (new ControllerResolver($this->container))->resolve($routeInfo);

            return (new ControllerInvoker())->invoke($controller, $routeInfo);
        }
        catch (ENotFoundError $exception) {
            throw new EHttpException(404, $exception->getMessage(), $exception);
        }
        catch (\Throwable $exception) {
            throw new EHttpException(message: $exception->getMessage(),previous: $exception);
        }

        // var_dump($routeInfo);
        // var_dump($controller);
        // var_dump($this->container->get('kernel.sapi_family'));
        // var_dump($this->container->get('kernel.sapi_type'));

        // var_export($this->request->getUri()->getPath());
        // var_export($this->request->getParsedBody());
        // var_export($this->request->getServerParams());
        // var_export($this->request->getHeaders());
        // var_export($this->request->getMethod());
        // var_export($this->request->getUserAgent());

        // return new Response();
    }
}
/** End of HttpHandler **/