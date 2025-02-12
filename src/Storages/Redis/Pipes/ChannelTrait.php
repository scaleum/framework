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

trait ChannelTrait
{
    use PipeTrait;

    /**
     * PSUBSCRIBE pattern [pattern ...]
     * Time complexity: O(N) where N is the number of patterns the client is already subscribed to.
     * @link http://redis.io/commands/psubscribe
     *
     * @param string|string[]       $patterns
     * @param \Closure|string|array $callback
     * @return string[]
     */
    public function psubscribe($patterns, $callback)
    {
        if (!is_callable( $callback )) {
            throw new \InvalidArgumentException( 'Function $callback is not callable' );
        }

        return $this->subscribeCommand( ['PSUBSCRIBE'], ['PUNSUBSCRIBE'], (array)$patterns, $callback );
    }

    /**
     * PUBLISH channel message
     * Time complexity: O(N+M) where N is the number of clients subscribed to the receiving channel
     * and M is the total number of subscribed patterns (by any client).
     *
     * @param string $channel
     * @param string $message
     * @return int The number of clients that received the message.
     */
    public function publish($channel, $message)
    {
        return $this->returnCommand( ['PUBLISH'], [$channel, $message] );
    }

    /**
     * PUBSUB subcommand [argument [argument ...]]
     * Time complexity: O(N) for the CHANNELS subcommand, where N is the number of active channels,
     * and assuming constant time pattern matching (relatively short channels and patterns).
     * O(N) for the NUMSUB subcommand, where N is the number of requested channels.
     * O(1) for the NUMPAT subcommand.
     *
     * @param string          $subcommand CHANNELS|NUMSUB|NUMPAT
     * @param string|string[] $arguments
     * @return array|int
     */
    public function pubsub($subcommand, $arguments = null)
    {
        $params = [$subcommand];
        if (isset( $arguments )) {
            $params[] = (array)$arguments;
        }

        return $this->returnCommand( ['PUBSUB'], $params );
    }

    /**
     * PUNSUBSCRIBE [pattern [pattern ...]]
     * Time complexity: O(N+M) where N is the number of patterns the client is already subscribed
     * and M is the number of total patterns subscribed in the system (by any client).
     *
     * @param string|string[]|null $patterns
     * @return
     */
    public function punsubscribe($patterns = null)
    {
        return $this->returnCommand( ['PUNSUBSCRIBE'], isset( $patterns ) ? (array)$patterns : null );
    }

    /**
     * SUBSCRIBE channel [channel ...]
     * Time complexity: O(N) where N is the number of channels to subscribe to.
     *
     * @link http://redis.io/commands/psubscribe
     *
     * @param string|string[]       $channels
     * @param \Closure|string|array $callback
     * @return string[]
     */
    public function subscribe($channels, $callback)
    {
        if (!is_callable( $callback )) {
            throw new \InvalidArgumentException( 'Function $callback is not callable' );
        }

        return $this->subscribeCommand( ['SUBSCRIBE'], ['UNSUBSCRIBE'], (array)$channels, $callback );
    }

    /**
     * UNSUBSCRIBE [channel [channel ...]]
     * Time complexity: O(N) where N is the number of clients already subscribed to a channel.
     *
     * @param string|string[]|null $channels
     * @return
     */
    public function unsubscribe($channels)
    {
        return $this->returnCommand( ['UNSUBSCRIBE'], isset( $channels ) ? (array)$channels : null );
    }
}