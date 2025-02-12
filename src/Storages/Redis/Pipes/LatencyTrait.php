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
trait LatencyTrait
{
    use PipeTrait;

    /**
     * LATENCY DOCTOR
     * @link http://redis.io/topics/latency-monitor
     *
     * @return string
     */
    public function latencyDoctor()
    {
        return $this->returnCommand( ['LATENCY', 'DOCTOR'] );
    }

    /**
     * LATENCY GRAPH event-name
     * @link http://redis.io/topics/latency-monitor
     *
     * @param string $eventName
     * @return string
     */
    public function latencyGraph($eventName)
    {
        return $this->returnCommand( ['LATENCY', 'GRAPH'], [$eventName] );
    }

    /**
     * LATENCY HISTORY event-name
     * @link http://redis.io/topics/latency-monitor
     *
     * @param string $eventName
     * @return array
     */
    public function latencyHistory($eventName)
    {
        return $this->returnCommand( ['LATENCY', 'HISTORY'], [$eventName] );
    }

    /**
     * LATENCY LATEST
     * @link http://redis.io/topics/latency-monitor
     *
     * @return array
     */
    public function latencyLatest()
    {
        return $this->returnCommand( ['LATENCY', 'LATEST'] );
    }

    /**
     * LATENCY RESET [event-name ... event-name]
     * @link http://redis.io/topics/latency-monitor
     *
     * @param string|string[] $eventNames
     * @return int
     */
    public function latencyReset($eventNames = null)
    {
        return $this->returnCommand( ['LATENCY', 'RESET'], (array)$eventNames );
    }
}