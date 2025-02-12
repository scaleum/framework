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

use Scaleum\Storages\Redis\Param;

trait StringsTrait
{
    use PipeTrait;

    /**
     * APPEND key value
     * Time complexity: O(1).
     * @link http://redis.io/commands/append
     *
     * @param string $key
     * @param string $value
     * @return int The length of the string after the append operation.
     */
    public function append($key, $value)
    {
        return $this->returnCommand( ['APPEND'], [$key, $value] );
    }

    /**
     * BITCOUNT key [start end]
     * Time complexity: O(N)
     * @link http://redis.io/commands/bitcount
     *
     * @param string   $key
     * @param mixed|int $start
     * @param mixed|int $end
     * @return int The number of bits set to 1.
     */
    public function bitcount($key, $start = null, $end = null)
    {
        if (isset( $start ) xor isset( $end )) {
            throw new \InvalidArgumentException( 'Start and End must be used together' );
        }
        $params = [$key];
        if (isset( $start, $end )) {
            $params[] = $start;
            $params[] = $end;
        }

        return $this->returnCommand( ['BITCOUNT'], $params );
    }

    /**
     * BITOP operation destkey key [key ...]
     * Time complexity: O(N)
     * @link http://redis.io/commands/bitop
     *
     * @param string          $operation AND, OR, XOR and NOT
     * @param string          $destkey
     * @param string|string[] $keys
     * @return int The size of the string stored in the destination key,
     *                                   that is equal to the size of the longest input string.
     */
    public function bitop($operation, $destkey, $keys)
    {
        return $this->returnCommand( ['BITOP'], [$operation, $destkey, (array)$keys] );
    }

    /**
     * BITPOS key bit [start] [end]
     * Time complexity: O(N)
     * @link http://redis.io/commands/bitpos
     *
     * @param string   $key
     * @param string   $bit
     * @param mixed|int $start
     * @param mixed|int $end
     * @return int The command returns the position of the first bit set to 1 or 0 according to the request.
     */
    public function bitpos($key, $bit, $start = null, $end = null)
    {
        $params = [$key, $bit];
        if (isset( $start )) {
            $params[] = $start;
            if (isset( $end )) {
                $params[] = $end;
            }
        }

        return $this->returnCommand( ['BITPOS'], $params );
    }

    /**
     * DECR key
     * Time complexity: O(1)
     * @link http://redis.io/commands/decr
     *
     * @param string $key
     * @return int The value of key after the decrement
     */
    public function decr($key)
    {
        return $this->returnCommand( ['DECR'], [$key] );
    }

    /**
     * DECRBY key decrement
     * Time complexity: O(1)
     * @link http://redis.io/commands/decrby
     *
     * @param string $key
     * @param int    $decrement
     * @return int The value of key after the decrement
     */
    public function decrby($key, $decrement)
    {
        return $this->returnCommand( ['DECRBY'], [$key, $decrement] );
    }

    /**
     * GET key
     * Time complexity: O(1)
     * @link http://redis.io/commands/get
     *
     * @param string $key
     * @return string|null
     */
    public function get($key)
    {
        return $this->returnCommand( ['GET'], [$key] );
    }

    /**
     * GETBIT key offset
     * Time complexity: O(1)
     * @link http://redis.io/commands/getbit
     *
     * @param string $key
     * @param int    $offset
     * @return int The bit value stored at offset.
     */
    public function getbit($key, $offset)
    {
        return $this->returnCommand( ['GETBIT'], [$key, $offset] );
    }

    /**
     * GETRANGE key start end
     * Time complexity: O(N) where N is the length of the returned string.
     * @link http://redis.io/commands/getrange
     *
     * @param string $key
     * @param int    $start
     * @param int    $end
     * @return string
     */
    public function getrange($key, $start, $end)
    {
        return $this->returnCommand( ['GETRANGE'], [$key, $start, $end] );
    }

    /**
     * GETSET key value
     * Time complexity: O(1)
     * @link http://redis.io/commands/getset
     *
     * @param string $key
     * @param string $value
     * @return string|null The old value stored at key, or nil when key did not exist.
     */
    public function getset($key, $value)
    {
        return $this->returnCommand( ['GETSET'], [$key, $value] );
    }

    /**
     * INCR key
     * Time complexity: O(1)
     * @link http://redis.io/commands/incr
     *
     * @param string $key
     * @return int The value of key after the increment
     */
    public function incr($key)
    {
        return $this->returnCommand( ['INCR'], [$key] );
    }

    /**
     * INCRBY key increment
     * Time complexity: O(1)
     * @link http://redis.io/commands/incrby
     *
     * @param string $key
     * @param int    $increment
     * @return int The value of key after the increment
     */
    public function incrby($key, $increment)
    {
        return $this->returnCommand( ['INCRBY'], [$key, $increment] );
    }

    /**
     * INCRBYFLOAT key increment
     * Time complexity: O(1)
     * @link http://redis.io/commands/incrbyfloat
     *
     * @param string        $key
     * @param integer|float $increment
     * @return string
     */
    public function incrbyfloat($key, $increment)
    {
        return $this->returnCommand( ['INCRBYFLOAT'], [$key, $increment] );
    }

    /**
     * MGET key [key ...]
     * Time complexity: O(N) where N is the number of keys to retrieve.
     * @link http://redis.io/commands/mget
     *
     * @param string|string[] $keys
     * @return array
     */
    public function mget($keys)
    {
        return $this->returnCommand( ['MGET'], (array)$keys );
    }

    /**
     * MSET key value [key value ...]
     * Time complexity: O(N) where N is the number of keys to set.
     * @link http://redis.io/commands/mset
     *
     * @param array $keyValues
     * @return bool always True since MSET can't fail.
     */
    public function mset(array $keyValues)
    {
        return $this->returnCommand( ['MSET'], Param::assocArray( $keyValues ) );
    }

    /**
     * MSETNX key value [key value ...]
     * Time complexity: O(N) where N is the number of keys to set.
     * @link http://redis.io/commands/msetnx
     *
     * @param array $keyValues
     * @return int 1 if the all the keys were set. 0 if no key was set (at least one key already existed).
     */
    public function msetnx(array $keyValues)
    {
        return $this->returnCommand( ['MSETNX'], Param::assocArray( $keyValues ) );
    }

    /**
     * PSETEX key milliseconds value
     * Time complexity: O(1)
     * @link http://redis.io/commands/psetex
     *
     * @param string $key
     * @param int    $milliseconds
     * @param string $value
     * @return bool
     */
    public function psetex($key, $milliseconds, $value)
    {
        return $this->returnCommand( ['PSETEX'], [$key, $milliseconds, $value] );
    }

    /**
     * SET key value [EX seconds | PX milliseconds] [NX|XX]
     * Time complexity: O(1)
     * @link  http://redis.io/commands/set
     *
     * @param string      $key
     * @param string      $value
     * @param mixed|int    $seconds
     * @param mixed|int    $milliseconds
     * @param mixed|string $exist NX - if not exist, XX - if it already exist.
     * @return bool|null
     * @throw InvalidArgumentException
     */
    public function set($key, $value, $seconds = null, $milliseconds = null, $exist = null)
    {
        $params = [$key, $value];
        if (isset( $seconds )) {
            $params[] = 'EX';
            $params[] = $seconds;
        }
        if (isset( $milliseconds )) {
            $params[] = 'PX';
            $params[] = $milliseconds;
        }
        if (isset( $exist )) {
            $params[] = $exist;
        }

        return $this->returnCommand( ['SET'], $params );
    }

    /**
     * SETBIT key offset value
     * Time complexity: O(1)
     * @link http://redis.io/commands/setbit
     *
     * @param string   $key
     * @param int      $offset
     * @param int|bool $bit 0/1 or true/false
     * @return int The original bit value stored at offset.
     */
    public function setbit($key, $offset, $bit)
    {
        return $this->returnCommand( ['SETBIT'], [$key, $offset, $bit] );
    }

    /**
     * SETEX key seconds value
     * Time complexity: O(1)
     * @link http://redis.io/commands/setex
     *
     * @param string $key
     * @param int    $seconds
     * @param string $value
     * @return bool
     */
    public function setex($key, $seconds, $value)
    {
        return $this->returnCommand( ['SETEX'], [$key, $seconds, $value] );
    }

    /**
     * SETNX key value
     * Time complexity: O(1)
     * @link http://redis.io/commands/setnx
     *
     * @param string $key
     * @param string $value
     * @return int 1 if the key was set, 0 if the key was not set
     */
    public function setnx($key, $value)
    {
        return $this->returnCommand( ['SETNX'], [$key, $value] );
    }

    /**
     * SETRANGE key offset value
     * Time complexity: O(1)
     * @link http://redis.io/commands/setrange
     *
     * @param string $key
     * @param int    $offset
     * @param string $value
     * @return int The length of the string after it was modified by the command.
     */
    public function setrange($key, $offset, $value)
    {
        return $this->returnCommand( ['SETRANGE'], [$key, $offset, $value] );
    }

    /**
     * STRLEN key
     * Time complexity: O(1)
     * @link http://redis.io/commands/strlen
     *
     * @param string $key
     * @return int The length of the string at key, or 0 when key does not exist.
     */
    public function strlen($key)
    {
        return $this->returnCommand( ['STRLEN'], [$key] );
    }

    /**
     * SUBSTR key start end
     * @param string $key
     * @param int    $start
     * @param int    $end
     * @return string
     * @deprecated
     * @see StringsCommandsTrait::getrange
     *
     */
    public function substr($key, $start, $end)
    {
        return $this->returnCommand( ['SUBSTR'], [$key, $start, $end] );
    }
}