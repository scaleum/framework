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

namespace Scaleum\Storages\Redis;

use Scaleum\Stdlib\Base\Hydrator;
use Scaleum\Storages\Redis\Drivers\Stream;

/**
 * Class ClientAbstract
 * @subpackage Avant\Storages\Redis
 */
abstract class ClientAbstract extends Hydrator implements ClientInterface
{
    protected ?DriverInterface $driver = null;
    protected string $host = '127.0.0.1';
    protected int $lifetime = 60;
    protected int $port = 6379;


    public function executeRaw($structure)
    {
        $response = $this->getDriver()->send( $structure );
        if ($response instanceof \ErrorException) {
            throw $response;
        }

        return $response;
    }

    public function executeRawString($command)
    {
        return $this->executeRaw( $this->parseRaw( $command ) );
    }

    public function parseRaw($command)
    {
        $structure = [];
        $line      = '';
        $quotes    = false;
        for ($i = 0, $length = strlen( $command ); $i <= $length; ++$i) {
            if ($i === $length) {
                if (isset( $line[0] )) {
                    $structure[] = $line;
                    $line        = '';
                }
                break;
            }
            if ($command[$i] === '"' && $i && $command[$i - 1] !== '\\') {
                $quotes = !$quotes;
                if (!$quotes && !isset( $line[0] ) && $i + 1 === $length) {
                    $structure[] = $line;
                    $line        = '';
                }
            } elseif ($command[$i] === ' ' && !$quotes) {
                if (isset( $command[$i + 1] ) && trim( $command[$i + 1] )) {
                    if (count( $structure ) || isset( $line[0] )) {
                        $structure[] = $line;
                        $line        = '';
                    }
                }
            } else {
                $line .= $command[$i];
            }
        }
        array_walk( $structure, function (&$line) {
            $line = str_replace( '\\"', '"', $line );
        } );

        return $structure;
    }

    public function returnCommand(array $command, array $params = null, $parserId = null)
    {
        return $this->executeCommand( $command, $params, $parserId );
    }

    public function subscribeCommand(array $subCommand, array $unsubCommand, array $params = null, $callback)
    {
        $this->getDriver()->subscribe( $this->getStructure( $subCommand, $params ), $callback );

        return $this->executeCommand( $unsubCommand, $params );
    }

    protected function executeCommand(array $command, array $params = null, $parserId = null)
    {
        $response = $this->getDriver()->send( $this->getStructure( $command, $params ) );
        if ($response instanceof \ErrorException) {
            throw $response;
        }
        if (isset( $parserId )) {
            return Response::parse( $parserId, $response );
        }

        return $response;
    }

    protected function getDriver()
    {
        if ($this->driver == null) {
            $this->driver = new Stream( sprintf( 'tcp://%s:%d', $this->host, $this->port ), $this->lifetime );
        }

        return $this->driver;
    }

    protected function getStructure(array $command, array $params = null)
    {
        if ($params == null) {
            return $command;
        }

        foreach ($params as $param) {
            if (is_array( $param )) {
                foreach ($param as $p) {
                    $command[] = $p;
                }
            } else {
                $command[] = $param;
            }
        }

        return $command;
    }
}

/* End of file ClientAbstract.php */
