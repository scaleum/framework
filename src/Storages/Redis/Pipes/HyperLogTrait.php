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

trait HyperLogTrait
{
    use PipeTrait;

    /**
     * PFADD key element [element ...]
     * Time complexity: O(1) to add every element.
     * @link http://redis.io/commands/pfadd
     *
     * @param string          $key
     * @param string|string[] $elements
     * @return int
     */
    public function pfadd($key, $elements)
    {
        return $this->returnCommand( ['PFADD'], [$key, (array)$elements] );
    }

    /**
     * PFCOUNT key [key ...]
     * Time complexity: O(1) with every small average constant times when called with a single key.
     * O(N) with N being the number of keys, and much bigger constant times, when called with multiple keys.
     * @link http://redis.io/commands/pfcount
     *
     * @param string|string[] $keys
     * @return int
     */
    public function pfcount($keys)
    {
        return $this->returnCommand( ['PFCOUNT'], (array)$keys );
    }

    /**
     * PFMERGE destkey sourcekey [sourcekey ...]
     * Time complexity: O(N) to merge N HyperLogLogs, but with high constant times.
     * @link http://redis.io/commands/pfmerge
     *
     * @param string          $destkey
     * @param string|string[] $sourcekeys
     * @return bool The command just returns True.
     */
    public function pfmerge($destkey, $sourcekeys)
    {
        return $this->returnCommand( ['PFMERGE'], [$destkey, (array)$sourcekeys] );
    }
}