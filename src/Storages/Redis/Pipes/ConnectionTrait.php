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

trait ConnectionTrait
{
    use PipeTrait;

    /**
     * AUTH password
     * @link http://redis.io/commands/auth
     *
     * @param string $password
     * @return bool True
     */
    public function auth($password)
    {
        return $this->returnCommand( ['AUTH'], [$password] );
    }

    /**
     * ECHO message
     * @link http://redis.io/commands/echo
     *
     * method for reversed word <echo> in PHP
     *
     * @param string $message
     * @return string Returns message
     */
    public function echoMessage($message)
    {
        return $this->returnCommand( ['ECHO'], [$message] );
    }

    /**
     * PING [message]
     * @link http://redis.io/commands/ping
     *
     * @param string $message
     * @return string Returns message
     */
    public function ping($message = null)
    {
        return $this->returnCommand( ['PING'], isset( $message ) ? [$message] : null );
    }

    /**
     * QUIT
     * @link http://redis.io/commands/quit
     *
     * @return bool Always True
     */
    public function quit()
    {
        return $this->returnCommand( ['QUIT'] );
    }

    /**
     * SELECT index
     * @link http://redis.io/commands/select
     *
     * @param int $db
     * @return bool
     */
    public function select($db)
    {
        return $this->returnCommand( ['SELECT'], [$db] );
    }
}