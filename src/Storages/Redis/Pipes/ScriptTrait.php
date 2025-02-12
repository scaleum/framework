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
trait ScriptTrait
{
    use PipeTrait;

    /**
     * EVAL script numkeys key [key ...] arg [arg ...]
     * Time complexity: Depends on the script that is executed.
     * @link http://redis.io/commands/eval
     *
     * method for reversed word <eval> in PHP
     *
     * @param string     $script
     * @param array|null $keys
     * @param array|null $args
     * @return mixed
     */
    public function evalScript($script, $keys = null, $args = null)
    {
        $params = [$script];
        if (is_array( $keys )) {
            $params[] = count( $keys );
            $params[] = (array)$keys;
        } else {
            $params[] = 0;
        }
        if (is_array( $args )) {
            $params[] = (array)$args;
        }

        return $this->returnCommand( ['EVAL'], $params );
    }

    /**
     * EVALSHA sha1 numkeys key [key ...] arg [arg ...]
     * Time complexity: Depends on the script that is executed.
     * @link http://redis.io/commands/evalsha
     *
     * @param string     $sha
     * @param array|null $keys
     * @param array|null $args
     * @return mixed
     */
    public function evalsha($sha, $keys = null, $args = null)
    {
        $params = [$sha];
        if (is_array( $keys )) {
            $params[] = count( $keys );
            $params[] = (array)$keys;
        } else {
            $params[] = 0;
        }
        if (is_array( $args )) {
            $params[] = (array)$args;
        }

        return $this->returnCommand( ['EVALSHA'], $params );
    }

    /**
     * SCRIPT EXISTS script [script ...]
     * Time complexity: O(N) with N being the number of scripts to check
     * (so checking a single script is an O(1) operation).
     * @link http://redis.io/commands/script-exists
     *
     * @param string|string[] $scriptsSha
     * @return int|int[]
     */
    public function scriptExists($scriptsSha)
    {
        return $this->returnCommand( ['SCRIPT', 'EXISTS'], (array)$scriptsSha );
    }

    /**
     * SCRIPT FLUSH
     * Time complexity: O(N) with N being the number of scripts in cache.
     * @link http://redis.io/commands/script-flush
     *
     * @return bool True
     */
    public function scriptFlush()
    {
        return $this->returnCommand( ['SCRIPT', 'FLUSH'] );
    }

    /**
     * SCRIPT KILL
     * Time complexity: O(1)
     * @link http://redis.io/commands/script-kill
     *
     * @return bool
     */
    public function scriptKill()
    {
        return $this->returnCommand( ['SCRIPT', 'KILL'] );
    }

    /**
     * SCRIPT LOAD script
     * Time complexity: O(N) with N being the length in bytes of the script body.
     * @link http://redis.io/commands/script-load
     *
     * @param string $script
     * @return string
     */
    public function scriptLoad($script)
    {
        return $this->returnCommand( ['SCRIPT', 'LOAD'], [$script] );
    }
}