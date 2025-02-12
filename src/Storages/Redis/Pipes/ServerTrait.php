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

trait ServerTrait
{
    use PipeTrait;

    /**
     * BGREWRITEAOF
     * @link http://redis.io/commands/bgrewriteaof
     *
     * @return bool|string Always true
     */
    public function bgrewriteaof()
    {
        return $this->returnCommand( ['BGREWRITEAOF'] );
    }

    /**
     * BGSAVE
     * @link http://redis.io/commands/bgsave
     *
     * @return string
     */
    public function bgsave()
    {
        return $this->returnCommand( ['BGSAVE'] );
    }

    /**
     * CLIENT GETNAME
     * Time complexity: O(1)
     * @link http://redis.io/commands/client-getname
     *
     * @return string|null The connection name, or a null bulk reply if no name is set.
     */
    public function clientGetname()
    {
        // todo: check
        return $this->returnCommand( ['CLIENT', 'GETNAME'], [] );
    }

    /**
     * CLIENT KILL [ip:port] [ID client-id] [TYPE normal|slave|pubsub] [ADDR ip:port] [SKIPME yes/no]
     * Time complexity: O(N) where N is the number of client connections
     * @link http://redis.io/commands/client-kill
     *
     * @param string|array|null $addr
     * @param int|null          $clientId
     * @param string|null       $type normal|slave|pubsub
     * @param string|array|null $addr2
     * @param bool|null         $skipme
     * @return bool|int
     *                                When called with the three arguments format:
     *                                Simple string reply: True if the connection exists and has been closed
     *                                When called with the filter / value format:
     *                                Integer reply: the number of clients killed.
     */
    public function clientKill($addr = null, $clientId = null, $type = null, $addr2 = null, $skipme = null)
    {
        $params = [];
        if ($addr) {
            $params[] = Param::address( $addr );
        }
        if ($clientId) {
            $params[] = 'ID';
            $params[] = $clientId;
        }
        if ($type) {
            $params[] = 'TYPE';
            $params[] = $type;
        }
        if ($addr2) {
            $params[] = 'ADDR';
            $params[] = Param::address( $addr2 );
        }
        if (isset( $skipme )) {
            $params[] = 'SKIPME';
            $params[] = $skipme ? 'yes' : 'no';
        }

        return $this->returnCommand( ['CLIENT', 'KILL'], $params );
    }

    /**
     * CLIENT LIST
     * Time complexity: O(N) where N is the number of client connections
     * @link http://redis.io/commands/client-list
     *
     * @return string
     */
    public function clientList()
    {
        return $this->returnCommand( ['CLIENT', 'LIST'], null, Response::TYPE_CLIENT_LIST );
    }

    /**
     * CLIENT PAUSE timeout
     * Time complexity: O(1)
     * @link http://redis.io/commands/client-pause
     *
     * @param int $timeout
     * @return bool The command returns True or an error if the timeout is invalid.
     */
    public function clientPause($timeout)
    {
        return $this->returnCommand( ['CLIENT', 'PAUSE'], [$timeout] );
    }

    /**
     * CLIENT SETNAME connection-name
     * Time complexity: O(1)
     * @link http://redis.io/commands/client-setname
     *
     * @param string $connectionName
     * @param bool True if the connection name was successfully set.
     */
    public function clientSetname($connectionName)
    {
        return $this->returnCommand( ['CLIENT', 'SETNAME'], [$connectionName] );
    }

    /**
     * COMMAND
     * Time complexity: O(N) where N is the total number of Redis commands
     * @link http://redis.io/commands/command
     *
     * @return array
     */
    public function command()
    {
        return $this->returnCommand( ['COMMAND'] );
    }

    /**
     * COMMAND COUNT
     * Time complexity: O(1)
     * @link http://redis.io/commands/command-count
     *
     * @return int Number of commands returned by COMMAND
     */
    public function commandCount()
    {
        // todo: check
        return $this->returnCommand( ['COMMAND', 'COUNT'], [] );
    }

    /**
     * COMMAND GETKEYS command
     * Time complexity: O(N) where N is the number of arguments to the command
     * @link http://redis.io/commands/command-getkeys
     *
     * @param string $command
     * @return string[] List of keys from your command.
     */
    public function commandGetkeys($command)
    {
        return $this->returnCommand( ['COMMAND', 'GETKEYS'], Param::command( $command ) );
    }

    /**
     * COMMAND INFO command-name [command-name ...]
     * Time complexity: O(N) when N is number of commands to look up
     * @link http://redis.io/commands/command-info
     *
     * @param string|string[] $commandNames
     * @return array Nested list of command details.
     */
    public function commandInfo($commandNames)
    {
        return $this->returnCommand( ['COMMAND', 'INFO'], (array)$commandNames );
    }

    /**
     * CONFIG GET parameter
     * @link  http://redis.io/commands/config-get
     *
     * @param string|string[]
     * @return array
     */
    public function configGet($parameter)
    {
        return $this->returnCommand( ['CONFIG', 'GET'], [$parameter], Response::TYPE_ASSOC_ARRAY );
    }

    /**
     * CONFIG RESETSTAT
     * Time complexity: O(1)
     * @link http://redis.io/commands/config-resetstat
     *
     * @return bool always True
     */
    public function configResetstat()
    {
        return $this->returnCommand( ['CONFIG', 'RESETSTAT'] );
    }

    /**
     * CONFIG REWRITE
     * @link http://redis.io/commands/config-rewrite
     *
     * @return bool True when the configuration was rewritten properly. Otherwise an error is returned.
     */
    public function configRewrite()
    {
        return $this->returnCommand( ['CONFIG', 'REWRITE'] );
    }

    /**
     * CONFIG SET parameter value
     * @link http://redis.io/commands/config-set
     *
     * @param string $parameter
     * @param string $value
     * @return bool True when the configuration was set properly. Otherwise an error is returned.
     */
    public function configSet($parameter, $value)
    {
        return $this->returnCommand( ['CONFIG', 'SET'], [$parameter, $value] );
    }

    /**
     * DBSIZE
     * @link http://redis.io/commands/dbsize
     *
     * @return int The number of keys in the currently-selected database.
     */
    public function dbsize()
    {
        return $this->returnCommand( ['DBSIZE'] );
    }

    /**
     * DEBUG OBJECT key
     * @link http://redis.io/commands/debug-object
     *
     * @param string $key
     * @return string
     */
    public function debugObject($key)
    {
        return $this->returnCommand( ['DEBUG', 'OBJECT'], [$key] );
    }

    /**
     * DEBUG SEGFAULT
     * @link http://redis.io/commands/debug-segfault
     *
     * @return string
     */
    public function debugSegfault()
    {
        return $this->returnCommand( ['DEBUG', 'SEGFAULT'] );
    }

    /**
     * FLUSHALL
     * @link http://redis.io/commands/flushall
     *
     * @return bool
     */
    public function flushall()
    {
        return $this->returnCommand( ['FLUSHALL'] );
    }

    /**
     * FLUSHDB
     * @link http://redis.io/commands/flushdb
     *
     * @return bool
     */
    public function flushdb()
    {
        return $this->returnCommand( ['FLUSHDB'] );
    }

    /**
     * INFO [section]
     * @link http://redis.io/commands/info
     *
     * @param string $section
     * @return string
     */
    public function info($section = null)
    {
        return $this->returnCommand( ['INFO'], $section ? [$section] : null, Response::TYPE_INFO );
    }

    /**
     * LASTSAVE
     * @link http://redis.io/commands/lastsave
     *
     * @return int an UNIX time stamp.
     */
    public function lastsave()
    {
        return $this->returnCommand( ['LASTSAVE'] );
    }

    /**
     * MONITOR
     * @link http://redis.io/commands/monitor
     *
     * @param \Closure $callback
     * @return mixed
     */
    public function monitor(\Closure $callback)
    {
        return $this->subscribeCommand( ['MONITOR'], ['QUIT'], null, $callback );
    }

    /**
     * ROLE
     * @link http://redis.io/commands/role
     *
     * @return array
     */
    public function role()
    {
        return $this->returnCommand( ['ROLE'] );
    }

    /**
     * SAVE
     * @link http://redis.io/commands/save
     *
     * @return bool The commands returns True on success
     */
    public function save()
    {
        return $this->returnCommand( ['SAVE'] );
    }

    /**
     * SHUTDOWN [NOSAVE|SAVE]
     * @link http://redis.io/commands/shutdown
     *
     * @param string|null $save NOSAVE or SAVE
     */
    public function shutdown($save)
    {
        return $this->returnCommand( ['SHUTDOWN'], $save ? [$save] : null );
    }

    /**
     * SLAVEOF host port
     * @link http://redis.io/commands/slaveof
     *
     * @param string $host
     * @param string $port
     * @return bool
     */
    public function slaveof($host, $port)
    {
        return $this->returnCommand( ['SLAVEOF'], [$host, $port] );
    }

    /**
     * SLOWLOG subcommand [argument]
     * @link http://redis.io/commands/slowlog
     *
     * @param string      $subcommand GET|LEN|RESET
     * @param string|null $argument
     * @return mixed
     */
    public function slowlog($subcommand, $argument = null)
    {
        $params = [$subcommand];
        if (isset( $argument )) {
            $params[] = $argument;
        }

        return $this->returnCommand( ['SLOWLOG'], $params );
    }

    /**
     * SYNC
     * @link http://redis.io/commands/sync
     */
    public function sync()
    {
        return $this->returnCommand( ['SYNC'] );
    }

    /**
     * TIME
     * Time complexity: O(1)
     * @link http://redis.io/commands/time
     *
     * @return string
     */
    public function time()
    {
        return $this->returnCommand( ['TIME'], null, Response::TYPE_TIME );
    }
}