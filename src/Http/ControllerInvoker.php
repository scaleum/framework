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

use Scaleum\Core\Contracts\ResponderInterface;
use Scaleum\Stdlib\Exceptions\EMethodNotFoundError;
use Scaleum\Stdlib\Exceptions\ERuntimeError;

/**
 * ControllerInvoker
 *
 * @author Maxim Kirichenko <kirichenko.maxim@gmail.com>
 */
class ControllerInvoker {
    public function invoke(object $controller, array $routeInfo): ResponderInterface {
        if ($callback = $routeInfo['callback']) {
            $method = $callback['method'] ?? null;
            $args   = $callback['args'] ?? [];
            if ($method === null) {
                throw new ERuntimeError('Controller method is not defined');
            }

            if (! method_exists($controller, $method)) {
                throw new EMethodNotFoundError(sprintf('Method "%s" does not exist in controller "%s"', $method, get_class($controller)));
            }

            return call_user_func_array([$controller, $method], [ ...$args]);
        }
        throw new ERuntimeError('Controller callback is not defined');
    }
}
/** End of ControllerInvoker **/