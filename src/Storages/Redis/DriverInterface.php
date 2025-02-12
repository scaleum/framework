<?php
declare(strict_types=1);
/**
 * This file is part of microGears\Stdlib.
 *
 * (C) 2009-2025 Maxim Kirichenko <kirichenko.maxim@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Scaleum\Storages\Redis;

interface DriverInterface
{
    public function send(array $structure);
    public function sendMulti(array $structures);
    public function subscribe(array $structure, $callback);
}