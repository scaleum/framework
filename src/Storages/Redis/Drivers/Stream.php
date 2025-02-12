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

namespace Scaleum\Storages\Redis\Drivers;

use Scaleum\Storages\Redis\DriverInterface;

class Stream implements DriverInterface
{
    public const EOL                 = "\r\n";
    public const TYPE_SIMPLE_STRINGS = '+';
    public const TYPE_ERRORS         = '-';
    public const TYPE_INTEGERS       = ':';
    public const TYPE_BULK_STRINGS   = '$';
    public const TYPE_ARRAYS         = '*';

    protected $resource;
    protected string $server;
    protected ?int $ttl = null;

    public function __construct(string $server, ?int $ttl = null)
    {
        $this->server = $server;
        if (is_numeric($ttl)) {
            $this->ttl = (int)ceil($ttl * 1000000);
        }
    }

    public function __destruct()
    {
        if ($this->resource) {
            fclose($this->resource);
        }
    }

    public function send(array $structures)
    {
        $this->write($this->packArray($structures));

        return $this->read();
    }

    public function sendMulti(array $structures)
    {
        $raw = '';
        foreach ($structures as $structure) {
            $raw .= $this->pack($structure);
        }
        $this->write($raw);
        $response = [];
        for ($i = count($structures); $i > 0; --$i) {
            $response[] = $this->read();
        }

        return $response;
    }

    public function subscribe(array $structures, $callback)
    {
        $this->write($this->packArray($structures));
        do {
            try {
                $response = (array)$this->read();
                for ($i = count($response); $i < 4; ++$i) {
                    $response[] = null;
                }
            } catch (\ErrorException $Ex) {
                $response = [null, null, null, null];
            }
            $continue = call_user_func_array($callback, $response);
        } while ($continue);
    }

    protected function getResource()
    {
        if (!$this->resource) {
            if (!$this->resource = stream_socket_client($this->server)) {
                throw new \ErrorException(sprintf(
                    'Unable to connect to %s',
                    $this->server
                ));
            }
            if (isset($this->ttl)) {
                stream_set_timeout($this->resource, 0, $this->ttl);
            }
        }

        return $this->resource;
    }

    protected function pack($data)
    {
        if (is_string($data) || is_int($data) || is_bool($data) || is_float($data) || is_null($data)) {
            return $this->packString((string)$data);
        }
        if (is_array($data)) {
            return $this->packArray($data);
        }
        throw new \ErrorException(gettype($data));
    }

    protected function packArray($array)
    {
        $pack = self::TYPE_ARRAYS . count($array) . self::EOL;
        foreach ($array as $a) {
            $pack .= $this->pack($a);
        }

        return $pack;
    }

    protected function packNull()
    {
        return self::TYPE_BULK_STRINGS . '-1' . self::EOL;
    }

    protected function packString(string $str)
    {
        return self::TYPE_BULK_STRINGS . mb_strlen($str) . self::EOL . $str . self::EOL;
    }

    protected function read()
    {
        if (!$line = $this->readRawLine()) {
            throw new \ErrorException('Empty response. Please, check connection timeout.');
        }

        $type = $line[0];
        $data = mb_substr($line, 1, -2);

        if ($type === self::TYPE_BULK_STRINGS) {
            $length = intval($data);
            if ($length === -1) {
                return null;
            }

            return mb_substr($this->readRaw($length + 2), 0, -2);
        }

        if ($type === self::TYPE_SIMPLE_STRINGS) {
            if ($data === 'OK') {
                return true;
            }

            return $data;
        }

        if ($type === self::TYPE_INTEGERS) {
            return (int)$data;
        }

        if ($type === self::TYPE_ARRAYS) {
            $count = (int)$data;
            if ($count === -1) {
                return null;
            }
            $array = [];
            for ($i = 0; $i < $count; ++$i) {
                $array[] = $this->read();
            }

            return $array;
        }

        if ($type === self::TYPE_ERRORS) {
            return new \ErrorException($data);
        }

        throw new \ErrorException("Unknown protocol type $type");
    }

    protected function readRaw(int $length): string
    {
        $buffer = '';
        $read   = 0;
        if ($length > 0) {
            do {
                $block_size = ($length - $read) > 1024 ? 1024 : ($length - $read);
                $block      = fread($this->getResource(), $block_size);
                if ($block === false) {
                    throw new \Exception('Failed to read response from stream');
                } else {
                    $read   += mb_strlen($block);
                    $buffer .= $block;
                }
            } while ($read < $length);
        }

        return $buffer;
    }

    protected function readRawLine(): mixed
    {
        return fgets($this->getResource());
    }

    protected function write($string): mixed
    {
        return fwrite($this->getResource(), $string, mb_strlen($string));
    }
}

/* End of file Stream.php */
