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
use Scaleum\Storages\Redis\Response;

trait SortedTrait
{
    use PipeTrait;

    /**
     * ZADD key [NX|XX] [CH] [INCR] score member [score member ...]
     * Time complexity: O(log(N)) for each item added, where N is the number of elements in the sorted set.
     * @link http://redis.io/commands/zadd
     *
     * @param string      $key
     * @param array       $members array(member => score [, member => score ...])
     * @param string|null $nx      NX or XX
     * @param bool|false  $ch
     * @param bool|false  $incr
     * @return int|string
     */
    public function zadd($key, array $members, $nx = null, $ch = false, $incr = false)
    {
        $params = [$key];
        if ($nx) {
            $params[] = $nx;
        }
        if ($ch) {
            $params[] = 'CH';
        }
        if ($incr) {
            $params[] = 'INCR';
        }
        $params[] = Param::assocArrayFlip($members);

        return $this->returnCommand(['ZADD'], $params);
    }

    /**
     * ZCARD key
     * Time complexity: O(1)
     * @link http://redis.io/commands/zcard
     *
     * @param string $key
     * @return int The cardinality (number of elements) of the sorted set, or 0 if key does not exist.
     */
    public function zcard($key)
    {
        return $this->returnCommand(['ZCARD'], [$key]);
    }

    /**
     * ZCOUNT key min max
     * Time complexity: O(log(N)) with N being the number of elements in the sorted set.
     * @link http://redis.io/commands/zcount
     *
     * @param int        $key
     * @param int|string $min
     * @param int|string $max
     * @return int The number of elements in the specified score range.
     */
    public function zcount($key, $min, $max)
    {
        return $this->returnCommand(['ZCOUNT'], [$key, $min, $max]);
    }

    /**
     * ZINCRBY key increment member
     * Time complexity: O(log(N)) where N is the number of elements in the sorted set.
     * @link http://redis.io/commands/zincrby
     *
     * @param string           $key
     * @param int|float|string $increment
     * @param string           $member
     * @return string The new score of member
     */
    public function zincrby($key, $increment, $member)
    {
        return $this->returnCommand(['ZINCRBY'], [$key, $increment, $member]);
    }

    /**
     * ZINTERSTORE destination numkeys key [key ...] [WEIGHTS weight [weight ...]] [AGGREGATE SUM|MIN|MAX]
     * Time complexity: O(N*K)+O(M*log(M)) worst case with N being the smallest input sorted set,
     * K being the number of input sorted sets and M being the number of elements in the resulting sorted set.
     * @link http://redis.io/commands/zinterstore
     *
     * @param string          $destination
     * @param string|string[] $keys
     * @param int|int[]|null  $weights
     * @param string|null     $aggregate
     * @return int The number of elements in the resulting sorted set at destination.
     */
    public function zinterstore($destination, $keys, $weights = null, $aggregate = null)
    {
        $keys   = (array)$keys;
        $params = [$destination, count($keys), (array)$keys];
        if ($weights) {
            $params[] = 'WEIGHTS';
            $params[] = (array)$weights;
        }
        if ($aggregate) {
            $params[] = 'AGGREGATE';
            $params[] = $aggregate;
        }

        return $this->returnCommand(['ZINTERSTORE'], $params);
    }

    /**
     * ZLEXCOUNT key min max
     * Time complexity: O(log(N)) with N being the number of elements in the sorted set.
     * @link http://redis.io/commands/zlexcount
     *
     * @param string $key
     * @param string $min
     * @param string $max
     * @return int The number of elements in the specified score range.
     */
    public function zlexcount($key, $min, $max)
    {
        return $this->returnCommand(['ZLEXCOUNT'], [$key, $min, $max]);
    }

    /**
     * ZRANGE key start stop [WITHSCORES]
     * Time complexity: O(log(N)+M) with N being the number of elements
     * in the sorted set and M the number of elements returned.
     * @link http://redis.io/commands/zrange
     *
     * @param string     $key
     * @param int        $start
     * @param int        $stop
     * @param bool|false $withscores
     * @return array List of elements in the specified range (optionally with their scores,
     * in case the WITHSCORES option is given).
     */
    public function zrange($key, $start, $stop, $withscores = false)
    {
        $params = [$key, $start, $stop];
        if ($withscores) {
            $params[] = 'WITHSCORES';
        }

        return $this->returnCommand(['ZRANGE'], $params, $withscores ? Response::TYPE_ASSOC_ARRAY : null);
    }

    /**
     * ZRANGEBYLEX key min max [LIMIT offset count]
     * Time complexity: O(log(N)+M) with N being the number of elements in the sorted set and
     * M the number of elements being returned.
     * If M is constant (e.g. always asking for the first 10 elements with LIMIT), you can consider it O(log(N)).
     * @link http://redis.io/commands/zrangebylex
     *
     * @param string    $key
     * @param string    $min
     * @param string    $max
     * @param int|array $limit
     * @return string[] List of elements in the specified score range.
     */
    public function zrangebylex($key, $min, $max, $limit = null)
    {
        $params = [$key, $min, $max];
        if ($limit) {
            $params[] = 'LIMIT';
            $params[] = Param::limit($limit);
        }

        return $this->returnCommand(['ZRANGEBYLEX'], $params);
    }

    /**
     * ZRANGEBYSCORE key min max [WITHSCORES] [LIMIT offset count]
     * Time complexity: O(log(N)+M) with N being the number of elements in the sorted set and
     * M the number of elements being returned.
     * If M is constant (e.g. always asking for the first 10 elements with LIMIT), you can consider it O(log(N)).
     * @link http://redis.io/commands/zrangebyscore
     *
     * @param string         $key
     * @param string|int     $min
     * @param string|int     $max
     * @param bool|false     $withscores
     * @param int|array|null $limit
     * @return string[]|array List of elements in the specified score range (optionally with their scores).
     */
    public function zrangebyscore($key, $min, $max, $withscores = false, $limit = null)
    {
        $params = [$key, $min, $max];
        if ($withscores) {
            $params[] = 'WITHSCORES';
        }
        if ($limit) {
            $params[] = 'LIMIT';
            $params[] = Param::limit($limit);
        }

        return $this->returnCommand(['ZRANGEBYSCORE'], $params, $withscores ? Response::TYPE_ASSOC_ARRAY : null);
    }

    /**
     * ZRANK key member
     * Time complexity: O(log(N))
     * @link http://redis.io/commands/zrank
     *
     * @param string $key
     * @param string $member
     * @return int|null
     */
    public function zrank($key, $member)
    {
        return $this->returnCommand(['ZRANK'], [$key, $member]);
    }

    /**
     * ZREM key member [member ...]
     * Time complexity: O(M*log(N)) with N being the number of elements in the sorted set
     * and M the number of elements to be removed.
     * @link http://redis.io/commands/zrem
     *
     * @param string          $key
     * @param string|string[] $members
     * @return int The number of members removed from the sorted set, not including non existing members.
     */
    public function zrem($key, $members)
    {
        return $this->returnCommand(['ZREM'], [$key, (array)$members]);
    }

    /**
     * ZREMRANGEBYLEX key min max
     * Time complexity: O(log(N)+M) with N being the number of elements in the sorted set
     * and M the number of elements removed by the operation.
     * @link http://redis.io/commands/zremrangebylex
     *
     * @param string $key
     * @param string $min
     * @param string $max
     * @return int The number of elements removed.
     */
    public function zremrangebylex($key, $min, $max)
    {
        return $this->returnCommand(['ZREMRANGEBYLEX'], [$key, $min, $max]);
    }

    /**
     * ZREMRANGEBYRANK key start stop
     * Time complexity: O(log(N)+M) with N being the number of elements in the sorted set
     * and M the number of elements removed by the operation.
     * @link http://redis.io/commands/zremrangebyrank
     *
     * @param string $key
     * @param int    $start
     * @param int    $stop
     * @return int The number of elements removed.
     */
    public function zremrangebyrank($key, $start, $stop)
    {
        return $this->returnCommand(['ZREMRANGEBYRANK'], [$key, $start, $stop]);
    }

    /**
     * ZREMRANGEBYSCORE key min max
     * Time complexity: O(log(N)+M) with N being the number of elements in the sorted set
     * and M the number of elements removed by the operation.
     * @link http://redis.io/commands/zremrangebyscore
     *
     * @param string     $key
     * @param string|int $min
     * @param string|int $max
     * @return int The number of elements removed.
     */
    public function zremrangebyscore($key, $min, $max)
    {
        return $this->returnCommand(['ZREMRANGEBYSCORE'], [$key, $min, $max]);
    }

    /**
     *  ZREVRANGE key start stop [WITHSCORES]
     * Time complexity: O(log(N)+M) with N being the number of elements in the sorted set
     * and M the number of elements returned.
     * @link http://redis.io/commands/zrevrange
     *
     * @param string     $key
     * @param int        $start
     * @param int        $stop
     * @param bool|false $withscores
     * @return array List of elements in the specified range (optionally with their scores,
     * in case the WITHSCORES option is given).
     */
    public function zrevrange($key, $start, $stop, $withscores = false)
    {
        $params = [$key, $start, $stop];
        if ($withscores) {
            $params[] = 'WITHSCORES';
        }

        return $this->returnCommand(['ZREVRANGE'], $params, $withscores ? Response::TYPE_ASSOC_ARRAY : null);
    }

    /**
     * ZREVRANGEBYLEX key max min [LIMIT offset count]
     * Time complexity: O(log(N)+M) with N being the number of elements in the sorted set
     * and M the number of elements being returned.
     * If M is constant (e.g. always asking for the first 10 elements with LIMIT), you can consider it O(log(N)).
     * @link http://redis.io/commands/zrevrangebylex
     *
     * @param string    $key
     * @param string    $max
     * @param string    $min
     * @param int|array $limit
     * @return string[] List of elements in the specified score range.
     */
    public function zrevrangebylex($key, $max, $min, $limit = null)
    {
        $params = [$key, $max, $min];
        if ($limit) {
            $params[] = 'LIMIT';
            $params[] = Param::limit($limit);
        }

        return $this->returnCommand(['ZREVRANGEBYLEX'], $params);
    }

    /**
     * ZREVRANGEBYSCORE key max min [WITHSCORES] [LIMIT offset count]
     * Time complexity: O(log(N)+M) with N being the number of elements in the sorted set
     * and M the number of elements being returned.
     * If M is constant (e.g. always asking for the first 10 elements with LIMIT), you can consider it O(log(N)).
     * @link http://redis.io/commands/zrevrangebyscore
     *
     * @param string            $key
     * @param string            $max
     * @param string            $min
     * @param bool|false        $withscores
     * @param string|array|null $limit
     * @return string[]|array list of elements in the specified score range (optionally with their scores).
     */
    public function zrevrangebyscore($key, $max, $min, $withscores = false, $limit = null)
    {
        $params = [$key, $max, $min];
        if ($withscores) {
            $params[] = 'WITHSCORES';
        }
        if ($limit) {
            $params[] = 'LIMIT';
            $params[] = Param::limit($limit);
        }

        return $this->returnCommand(['ZREVRANGEBYSCORE'], $params, $withscores ? Response::TYPE_ASSOC_ARRAY : null);
    }

    /**
     * ZREVRANK key member
     * Time complexity: O(log(N))
     * @link http://redis.io/commands/zrevrank
     *
     * @param string $key
     * @param string $member
     * @return int|null
     */
    public function zrevrank($key, $member)
    {
        return $this->returnCommand(['ZREVRANK'], [$key, $member]);
    }

    /**
     * ZSCAN key cursor [MATCH pattern] [COUNT count]
     * Time complexity: O(1) for every call. O(N) for a complete iteration,
     * including enough command calls for the cursor to return back to 0.
     * N is the number of elements inside the collection.
     * @link http://redis.io/commands/zscan
     *
     * @param string      $key
     * @param int         $cursor
     * @param string|null $pattern
     * @param int|null    $count
     * @return mixed
     */
    public function zscan($key, $cursor, $pattern = null, $count = null)
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

        return $this->returnCommand(['ZSCAN'], $params);
    }

    /**
     * ZSCORE key member
     * Time complexity: O(1)
     * @link http://redis.io/commands/zscore
     *
     * @param string $key
     * @param string $member
     * @return int The score of member (a double precision floating point number), represented as string.
     */
    public function zscore($key, $member)
    {
        return $this->returnCommand(['ZSCORE'], [$key, $member]);
    }

    /**
     * ZUNIONSTORE destination numkeys key [key ...] [WEIGHTS weight [weight ...]] [AGGREGATE SUM|MIN|MAX]
     * Time complexity: O(N)+O(M log(M)) with N being the sum of the sizes of the input sorted sets,
     * and M being the number of elements in the resulting sorted set.
     * @link http://redis.io/commands/zunionstore
     *
     * @param string          $destination
     * @param string|string[] $keys
     * @param int|int[]       $weights
     * @param string          $aggregate
     * @return int The number of elements in the resulting sorted set at destination.
     */
    public function zunionstore($destination, $keys, $weights = null, $aggregate = null)
    {
        $keys   = (array)$keys;
        $params = [$destination, count($keys), (array)$keys];
        if ($weights) {
            $params[] = 'WEIGHTS';
            $params[] = (array)$weights;
        }
        if ($aggregate) {
            $params[] = 'AGGREGATE';
            $params[] = $aggregate;
        }

        return $this->returnCommand(['ZUNIONSTORE'], $params);
    }
}
