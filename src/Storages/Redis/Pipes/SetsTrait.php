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
trait SetsTrait
{
    use PipeTrait;

    /**
     * SADD key member [member ...]
     * Time complexity: O(N) where N is the number of members to be added.
     * @link http://redis.io/commands/sadd
     *
     * @param string          $key
     * @param string|string[] $members
     * @return int The number of elements that were added to the set,
     * not including all the elements already present into the set.
     */
    public function sadd($key, $members)
    {
        return $this->returnCommand( ['SADD'], [$key, (array)$members] );
    }

    /**
     * SCARD key
     * Time complexity: O(1)
     * @link http://redis.io/commands/scard
     *
     * @param string $key
     * @return int The cardinality (number of elements) of the set, or 0 if key does not exist.
     */
    public function scard($key)
    {
        return $this->returnCommand( ['SCARD'], [$key] );
    }

    /**
     * SDIFF key [key ...]
     * Time complexity: O(N) where N is the total number of elements in all given sets.
     * @link http://redis.io/commands/sdiff
     *
     * @param string|string[] $keys
     * @return array List with members of the resulting set.
     */
    public function sdiff($keys)
    {
        return $this->returnCommand( ['SDIFF'], (array)$keys );
    }

    /**
     * SDIFFSTORE destination key [key ...]
     * Time complexity: O(N) where N is the total number of elements in all given sets.
     * @link http://redis.io/commands/sdiffstore
     *
     * @param string          $destination
     * @param string|string[] $keys
     * @return int The number of elements in the resulting set.
     */
    public function sdiffstore($destination, $keys)
    {
        return $this->returnCommand( ['SDIFFSTORE'], [$destination, (array)$keys] );
    }

    /**
     * SINTER key [key ...]
     * Time complexity: O(N*M) worst case where N is the cardinality of the smallest set and M is the number of sets.
     * @link http://redis.io/commands/sinter
     *
     * @param string|string[] $keys
     * @return array List with members of the resulting set.
     */
    public function sinter($keys)
    {
        return $this->returnCommand( ['SINTER'], (array)$keys );
    }

    /**
     * SINTERSTORE destination key [key ...]
     * Time complexity: O(N*M) worst case where N is the cardinality of the smallest set and M is the number of sets.
     * @link http://redis.io/commands/sinterstore
     *
     * @param string          $destination
     * @param string|string[] $keys
     * @return int The number of elements in the resulting set.
     */
    public function sinterstore($destination, $keys)
    {
        return $this->returnCommand( ['SINTERSTORE'], [$destination, (array)$keys] );
    }

    /**
     * SISMEMBER key member
     * Time complexity: O(1)
     * @link http://redis.io/commands/sismember
     *
     * @param string $key
     * @param string $member
     * @return int 1 if the element is a member of the set.
     * 0 if the element is not a member of the set, or if key does not exist.
     */
    public function sismember($key, $member)
    {
        return $this->returnCommand( ['SISMEMBER'], [$key, $member] );
    }

    /**
     * SMEMBERS key
     * Time complexity: O(N) where N is the set cardinality.
     * @link http://redis.io/commands/smembers
     *
     * @param string $key
     * @return string[] All elements of the set.
     */
    public function smembers($key)
    {
        return $this->returnCommand( ['SMEMBERS'], [$key] );
    }

    /**
     * SMOVE source destination member
     * Time complexity: O(1)
     * @link http://redis.io/commands/smove
     *
     * @param string $source
     * @param string $destination
     * @param string $member
     * @return int 1 if the element is moved.
     * 0 if the element is not a member of source and no operation was performed.
     */
    public function smove($source, $destination, $member)
    {
        return $this->returnCommand( ['SMOVE'], [$source, $destination, $member] );
    }

    /**
     * SPOP key
     * Time complexity: O(1)
     * @link http://redis.io/commands/spop
     *
     * @param string $key
     * @return string|null The removed element, or null when key does not exist.
     */
    public function spop($key)
    {
        return $this->returnCommand( ['SPOP'], [$key] );
    }

    /**
     * SRANDMEMBER key [count]
     * Time complexity: Without the count argument O(1),
     * otherwise O(N) where N is the absolute value of the passed count.
     * @link http://redis.io/commands/srandmember
     *
     * @param string   $key
     * @param int|null $count
     * @return string|string[]
     */
    public function srandmember($key, $count = null)
    {
        $params = [$key];
        if ($count) {
            $params[] = $count;
        }

        return $this->returnCommand( ['SRANDMEMBER'], $params );
    }

    /**
     * SREM key member [member ...]
     * Time complexity: O(N) where N is the number of members to be removed.
     * @link http://redis.io/commands/srem
     *
     * @param string          $key
     * @param string|string[] $members
     * @return int The number of members that were removed from the set, not including non existing members.
     */
    public function srem($key, $members)
    {
        return $this->returnCommand( ['SREM'], [$key, (array)$members] );
    }

    /**
     * SSCAN key cursor [MATCH pattern] [COUNT count]
     * Time complexity: O(1) for every call. O(N) for a complete iteration,
     * including enough command calls for the cursor to return back to 0.
     * N is the number of elements inside the collection.
     * @link http://redis.io/commands/sscan
     *
     * @param string      $key
     * @param int         $cursor
     * @param string|null $pattern
     * @param int|null    $count
     * @return mixed
     */
    public function sscan($key, $cursor, $pattern = null, $count = null)
    {
        $params = [$key, $cursor];
        if ($pattern) {
            $params[] = 'MATCH';
            $params[] = $pattern;
        }
        if ($count) {
            $params[] = 'COUNT';
            $params[] = $count;
        }

        return $this->returnCommand( ['SSCAN'], $params );
    }

    /**
     * SUNION key [key ...]
     * Time complexity: O(N) where N is the total number of elements in all given sets.
     * @link http://redis.io/commands/sunion
     *
     * @param string|string[] $keys
     * @return string[] List with members of the resulting set.
     */
    public function sunion($keys)
    {
        return $this->returnCommand( ['SUNION'], (array)$keys );
    }

    /**
     * SUNIONSTORE destination key [key ...]
     * Time complexity: O(N) where N is the total number of elements in all given sets.
     * @link http://redis.io/commands/sunionstore
     *
     * @param string          $destination
     * @param string|string[] $keys
     * @return int The number of elements in the resulting set.
     */
    public function sunionstore($destination, $keys)
    {
        return $this->returnCommand( ['SUNIONSTORE'], [$destination, (array)$keys] );
    }
}