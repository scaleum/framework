<?php

declare(strict_types=1);
/**
 * This file is part of Scaleum\Storages\Redis.
 *
 * (C) 2009-2025 Maxim Kirichenko <kirichenko.maxim@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Scaleum\Storages\Redis;

interface ClientInterface
{
    public function returnCommand(array $command, array $params = null, $parserId = null);

    public function subscribeCommand(array $subCommand, array $unsubCommand, array $params = null, $callback);
}
