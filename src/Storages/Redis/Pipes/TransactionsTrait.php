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

namespace Scaleum\Storages\Redis\Pipes;

trait TransactionsTrait
{
    use PipeTrait;

    /**
     * DISCARD
     * @link http://redis.io/commands/discard
     *
     * @return bool Always True
     */
    public function discard()
    {
        return $this->returnCommand(['DISCARD']);
    }

    /**
     * EXEC
     * @link http://redis.io/commands/exec
     *
     * @return mixed
     */
    public function exec()
    {
        return $this->returnCommand(['EXEC']);
    }

    /**
     * MULTI
     * @link http://redis.io/commands/multi
     *
     * @return bool Always True
     */
    public function multi()
    {
        return $this->returnCommand(['MULTI']);
    }

    /**
     * UNWATCH
     * Time complexity: O(1)
     * @link http://redis.io/commands/unwatch
     *
     * @return bool Always True
     */
    public function unwatch()
    {
        return $this->returnCommand(['UNWATCH']);
    }

    /**
     * WATCH key [key ...]
     * Time complexity: O(1) for every key.
     * @link http://redis.io/commands/watch
     *
     * @param string|string[] $keys
     * @return bool Always True
     */
    public function watch($keys)
    {
        return $this->returnCommand(['WATCH'], (array)$keys);
    }
}
