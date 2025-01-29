<?php
declare(strict_types=1);
/**
 * This file is part of Scaleum Framework.
 *
 * (C) 2009-2025 Maxim Kirichenko <kirichenko.maxim@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Scaleum\Routing;


/**
 * RouteInterface
 *
 * @author Maxim Kirichenko <kirichenko.maxim@gmail.com>
 */
interface RouteInterface
{
    public function getPath():string;
    public function setPath(string $path):self;
    public function getName():?string;
    public function setName(string $name):self;
    public function getMethods():array;
    public function setMethods(string | array $methods):self;
    public function getCallback():?array;
    public function setCallback(array $callback):self;
    public function getUrl(array $params):string;
}
/** End of RouteInterface **/   