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

trait KeysTrait
{
    use PipeTrait;

    /**
     * DEL key [key ...]
     * Time complexity: O(N) where N is the number of keys that will be removed.
     * @link http://redis.io/commands/del
     *
     * @param string|string[] $keys
     * @return int The number of keys that were removed.
     */
    public function del($keys)
    {
        return $this->returnCommand( ['DEL'], (array)$keys );
    }

    /**
     * DUMP key
     * Time complexity: O(1) to access the key and additional O(N*M) to serialized it,
     * where N is the number of Redis objects composing the value and M their average size.
     * @link http://redis.io/commands/dump
     *
     * @param string $key
     * @return string The serialized value.
     */
    public function dump($key)
    {
        return $this->returnCommand( ['DUMP'], [$key] );
    }

    /**
     * EXISTS key [key ...]
     * Time complexity: O(1)
     * @link http://redis.io/commands/exists
     *
     * @param string|string[] $keys
     * @return int 1 if the key exists. 0 if the key does not exist.
     * Or the number of keys existing among the ones specified as arguments.
     */
    public function exists($keys)
    {
        return $this->returnCommand( ['EXISTS'], (array)$keys );
    }

    /**
     * EXPIRE key seconds
     * Time complexity: O(1)
     * @link http://redis.io/commands/expire
     *
     * @param string $key
     * @param int    $seconds
     * @return int 1 if the timeout was set. 0 if key does not exist or the timeout could not be set.
     */
    public function expire($key, $seconds)
    {
        return $this->returnCommand( ['EXPIRE'], [$key, $seconds,] );
    }

    /**
     * EXPIREAT key timestamp
     * Time complexity: O(1)
     * @link http://redis.io/commands/expireat
     *
     * @param string $key
     * @param int    $timestamp
     * @return int 1 if the timeout was set. 0 if key does not exist or the timeout could not be set (see: EXPIRE).
     */
    public function expireAt($key, $timestamp)
    {
        return $this->returnCommand( ['EXPIREAT'], [$key, $timestamp,] );
    }

    /**
     * KEYS pattern
     * Time complexity: O(N)
     * @link http://redis.io/commands/keys
     *
     * @param string $pattern
     * @return array List of keys matching pattern.
     */
    public function keys($pattern)
    {
        return $this->returnCommand( ['KEYS'], [$pattern] );
    }

    /**
     * MIGRATE host port key destination-db timeout [COPY] [REPLACE]
     * @link http://redis.io/commands/migrate
     *
     * @param string $host
     * @param int    $port
     * @param string $key
     * @param int    $destinationDb
     * @param int    $timeout In milliseconds
     * @param bool   $copy    Available in 3.0 and are not available in 2.6 or 2.8
     * @param bool   $replace Available in 3.0 and are not available in 2.6 or 2.8
     * @return bool The command returns True on success.
     */
    public function migrate($host, $port, $key, $destinationDb, $timeout, $copy = false, $replace = false)
    {
        $params = [$host, $port, $key, $destinationDb, $timeout];
        if ($copy) {
            $params[] = 'COPY';
        }
        if ($replace) {
            $params[] = 'REPLACE';
        }

        return $this->returnCommand( ['MIGRATE'], $params );
    }

    /**
     * MOVE key db
     * Time complexity: O(1)
     * @link http://redis.io/commands/move
     *
     * @param string $key
     * @param int    $db
     * @return int 1 if key was moved. 0 if key was not moved.
     */
    public function move($key, $db)
    {
        return $this->returnCommand( ['MOVE'], [$key, $db] );
    }

    /**
     * OBJECT subcommand [arguments [arguments ...]]
     * Time complexity: O(1) for all the currently implemented subcommands.
     * @link http://redis.io/commands/object
     *
     * @param string               $subcommand REFCOUNT|ENCODING|IDLETIME
     * @param mixed|string|string[] $arguments
     * @return int|string
     */
    public function object($subcommand, $arguments = null)
    {
        $params = [$subcommand];
        if ($arguments) {
            $params[] = (array)$arguments;
        }

        return $this->returnCommand( ['OBJECT'], $params );
    }

    /**
     * PERSIST key
     * Time complexity: O(1)
     * @link http://redis.io/commands/persist
     *
     * @param string $key
     * @return int 1 if the timeout was removed.
     * 0 if key does not exist or does not have an associated timeout.
     */
    public function persist($key)
    {
        return $this->returnCommand( ['PERSIST'], [$key] );
    }

    /**
     * PEXPIRE key milliseconds
     * Time complexity: O(1)
     * @link http://redis.io/commands/pexpire
     *
     * @param string $key
     * @param int    $milliseconds
     * @return int 1 if the timeout was set.
     * 0 if key does not exist or the timeout could not be set.
     */
    public function pexpire($key, $milliseconds)
    {
        return $this->returnCommand( ['PEXPIRE'], [$key, $milliseconds] );
    }

    /**
     * PEXPIREAT key milliseconds-timestamp
     * Time complexity: O(1)
     * @link http://redis.io/commands/pexpireat
     *
     * @param string $key
     * @param int    $millisecondsTimestamp
     * @return int 1 if the timeout was set. 0 if key does not exist or the timeout could not be set (see: EXPIRE).
     */
    public function pexpireat($key, $millisecondsTimestamp)
    {
        return $this->returnCommand( ['PEXPIREAT'], [$key, $millisecondsTimestamp] );
    }

    /**
     * PTTL key
     * Time complexity: O(1)
     * @link http://redis.io/commands/pttl
     *
     * @param string $key
     * @return int TTL in milliseconds, or a negative value in order to signal an error.
     */
    public function pttl($key)
    {
        return $this->returnCommand( ['PTTL'], [$key] );
    }

    /**
     * RANDOMKEY
     * Time complexity: O(1)
     * @link http://redis.io/commands/randomkey
     *
     * @return string|null The random key, or null when the database is empty.
     */
    public function randomkey()
    {
        return $this->returnCommand( ['RANDOMKEY'] );
    }

    /**
     * RENAME key newkey
     * Time complexity: O(1)
     * @link http://redis.io/commands/rename
     *
     * @param string $key
     * @param string $newkey
     * @return bool True
     */
    public function rename($key, $newkey)
    {
        return $this->returnCommand( ['RENAME'], [$key, $newkey,] );
    }

    /**
     * RENAMENX key newkey
     * Time complexity: O(1)
     * @link http://redis.io/commands/renamenx
     *
     * @param string $key
     * @param string $newkey
     * @return int 1 if key was renamed to newkey. 0 if newkey already exists.
     */
    public function renamenx($key, $newkey)
    {
        return $this->returnCommand( ['RENAMENX'], [$key, $newkey,] );
    }

    /**
     * RESTORE key ttl serialized-value [REPLACE]
     * Time complexity: O(1) to create the new key and additional O(N*M) to reconstruct the serialized value
     * @link http://redis.io/commands/restore
     *
     * @param string     $key
     * @param int        $ttl     In milliseconds
     * @param string     $serializedValue
     * @param bool|false $replace Redis 3.0 or greater
     * @return bool The command returns True on success.
     */
    public function restore($key, $ttl, $serializedValue, $replace = false)
    {
        $params = [$key, $ttl, $serializedValue];
        if ($replace) {
            $params[] = 'REPLACE';
        }

        return $this->returnCommand( ['RESTORE'], $params );
    }

    /**
     * SORT key [BY pattern] [LIMIT offset count] [GET pattern [GET pattern ...]] [ASC|DESC] [ALPHA] [STORE destination]
     * Time complexity: O(N+M*log(M)) or O(N)
     * @link http://redis.io/commands/sort
     *
     * @param string               $key
     * @param string|null          $pattern
     * @param int|array|null       $limit
     * @param string|string[]|null $patterns
     * @param bool|null            $asc
     * @param bool                 $alpha
     * @param string|null          $destination
     * @return mixed
     */
    public function sort($key, $pattern = null, $limit = null, $patterns = null, $asc = null, $alpha = false, $destination = null)
    {
        $params = [$key];
        if ($pattern) {
            $params[] = 'BY';
            $params[] = $pattern;
        }
        if ($limit) {
            $params[] = 'LIMIT';
            $params[] = Param::limit( $limit );
        }
        if ($patterns) {
            foreach ((array)$patterns as $p) {
                $params[] = 'GET';
                $params[] = $p;
            }
        }
        if (isset( $asc )) {
            $params[] = $asc ? 'ASC' : 'DESC';
        }
        if ($alpha) {
            $params[] = 'ALPHA';
        }
        if ($destination) {
            $params[] = 'STORE';
            $params[] = $destination;
        }

        return $this->returnCommand( ['SORT'], $params );
    }

    /**
     * TTL key
     * Time complexity: O(1)
     * @link http://redis.io/commands/ttl
     *
     * @param string $key
     * @return int TTL in seconds, or a negative value in order to signal an error
     */
    public function ttl($key)
    {
        return $this->returnCommand( ['TTL'], [$key] );
    }

    /**
     * TYPE key
     * Time complexity: O(1)
     * @link http://redis.io/commands/type
     *
     * @param string $key
     * @return string
     */
    public function type($key)
    {
        return $this->returnCommand( ['TYPE'], [$key] );
    }

    /**
     * WAIT numslaves timeout
     * Time complexity: O(1)
     * @link http://redis.io/commands/wait
     *
     * @param int $numslaves
     * @param int $timeout In milliseconds
     * @return int The command returns the number of slaves reached
     *                     by all the writes performed in the context of the current connection.
     */
    public function wait($numslaves, $timeout)
    {
        return $this->returnCommand( ['WAIT'], [$numslaves, $timeout] );
    }
}