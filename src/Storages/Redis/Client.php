<?php

declare(strict_types=1);
/**
 * This file is part of Scaleum\Storages\Redis\Pipes.
 *
 * (C) 2009-2025 Maxim Kirichenko <kirichenko.maxim@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Scaleum\Storages\Redis;

use Scaleum\Storages\Redis\Pipes\ChannelTrait;
use Scaleum\Storages\Redis\Pipes\ClusterTrait;
use Scaleum\Storages\Redis\Pipes\ConnectionTrait;
use Scaleum\Storages\Redis\Pipes\HashTrait;
use Scaleum\Storages\Redis\Pipes\HyperLogTrait;
use Scaleum\Storages\Redis\Pipes\KeysTrait;
use Scaleum\Storages\Redis\Pipes\LatencyTrait;
use Scaleum\Storages\Redis\Pipes\ListTrait;
use Scaleum\Storages\Redis\Pipes\ScriptTrait;
use Scaleum\Storages\Redis\Pipes\ServerTrait;
use Scaleum\Storages\Redis\Pipes\SetsTrait;
use Scaleum\Storages\Redis\Pipes\SortedTrait;
use Scaleum\Storages\Redis\Pipes\StringsTrait;
use Scaleum\Storages\Redis\Pipes\TransactionsTrait;

class Client extends ClientAbstract
{
    use ChannelTrait;
    use ClusterTrait;
    use ConnectionTrait;
    use HashTrait;
    use HyperLogTrait;
    use KeysTrait;
    use LatencyTrait;
    use ListTrait;
    use ScriptTrait;
    use ServerTrait;
    use SetsTrait;
    use SortedTrait;
    use StringsTrait;
    use TransactionsTrait;

    protected int $db = 0;

    public function getDb(): int
    {
        return $this->db;
    }

    public function setDb(int $db)
    {
        $this->select($this->db = $db);
    }
}

/* End of file Client.php */
