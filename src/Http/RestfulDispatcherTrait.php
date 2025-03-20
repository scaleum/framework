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
use Scaleum\Stdlib\Exceptions\EHttpException;
use Scaleum\Stdlib\Helpers\StringCaseHelper;

/**
 * MethodDispatcherTrait
 *
 * @author Maxim Kirichenko <kirichenko.maxim@gmail.com>
 */
trait RestfulDispatcherTrait {
    abstract public function getRequest(): InboundRequest;

    public function __methodName($str) {
        return StringCaseHelper::camelize($this->getRequest()->getMethod() . '_' . preg_replace('/\.([^\.]+)$/', '', $str));
    }

    public function __dispatch(): ResponderInterface {
        $args = func_get_args();

        // Define route
        $route     = '';
        $argsTemp  = [];
        $argsCount = count($args);

        for ($i = 0; $i < $argsCount; $i++) {
            $route .= ($route != '' ? '_' : '') . $args[$i];
            if (method_exists($this, $this->__methodName($route))) {
                $argsTemp[] = [
                    'route' => $route,
                    'args'  => array_slice($args, $i + 1),
                ];
            }
        }

        if (count($argsTemp) && $args_data = end($argsTemp)) {
            $route = $args_data['route'];
            $args  = $args_data['args'];
        }

        // $pattern = '/^(.*)\.(' . implode('|', array_keys($this->response_formats)) . ')$/';
        // if (preg_match($pattern, $route, $matches)) {
        // 	$route = $matches[1];
        // }

        // Define method
        $method = $this->__methodName($route);

        // Sure it exists, but can they do anything with it?
        if (! method_exists($this, $method)) {
            throw new EHttpException(404, 'Unknown method' . ($method ? sprintf(" %s::%s", get_class($this), (string) $method) : ""));
        }

        // Fire
        return call_user_func_array([$this, $method], $args);
    }
}
/** End of MethodDispatcherTrait **/