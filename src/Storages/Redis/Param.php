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

class Param
{
    const MIN_MAX_PREG          = '/^([-+]inf|\(?-?\d+)$/';
    const SPECIFY_INTERVAL_PREG = '/^(-|\+|[\(\[]\w+)$/';
    /**
     * @var string[]
     */
    protected static $aggregateParams = ['SUM', 'MIN', 'MAX'];
    /**
     * @var string[]
     */
    protected static $bitOperationParams = ['AND', 'OR', 'XOR', 'NOT'];
    /**
     * @var string[]
     * @link http://redis.io/commands/geodist
     */
    protected static $geoUnits = ['m', 'km', 'mi', 'ft'];

    /**
     * @param $param
     * @return array
     * @throws \ErrorException
     */
    public static function address($param)
    {
        if ($param && is_string( $param )) {
            $param = explode( ':', trim( $param ), 2 );
        }
        if (is_array( $param )) {
            if (isset( $param[0], $param[1] )) {
                return [
                  static::string( $param[0] ),
                  static::port( $param[1] ),
                ];
            } elseif (isset( $param['ip'], $param['port'] )) {
                return [
                  static::string( $param['ip'] ),
                  static::port( $param['port'] ),
                ];
            }
        }
        throw new \ErrorException( 'Invalid address '.$param );
    }

    /**
     * @param $param
     * @return string
     * @throws \ErrorException
     */
    public static function aggregate($param)
    {
        $param = strtoupper( (string)$param );
        if (in_array( $param, static::$aggregateParams )) {
            return $param;
        }
        throw new \ErrorException("Invalid aggregate $param" );
    }

    /**
     * @param array $array
     * @return string[]
     */
    public static function assocArray(array $array)
    {
        $structure = [];
        foreach ($array as $key => $value) {
            $structure[] = $key;
            $structure[] = static::string( $value );
        }

        return $structure;
    }

    /**
     * @param array $array
     * @return string[]
     */
    public static function assocArrayFlip(array $array)
    {
        $structure = [];
        foreach ($array as $key => $value) {
            $structure[] = static::string( $value );
            $structure[] = $key;
        }

        return $structure;
    }

    /**
     * @param int|bool $bit
     * @return int
     */
    public static function bit($bit)
    {
        return (int)(bool)$bit;
    }

    /**
     * @param $operation
     * @return string
     * @throws \ErrorException
     */
    public static function bitOperation($operation)
    {
        $operation = strtoupper( (string)$operation );
        if (in_array( $operation, static::$bitOperationParams )) {
            return $operation;
        }
        throw new \ErrorException("Invalid bit operator $operation" );
    }

    /**
     * @param string $command
     * @return string[]
     */
    public static function command($command)
    {
        return explode( ' ', $command );
    }

    /**
     * @param int|float|string $param
     * @param int[]|string[]   $enum
     * @return string
     */
    public static function enum($param, array $enum)
    {
        if (!in_array( $param, $enum )) {
            throw new \ErrorException( 'Incorrect param "'.$param.'" for enum('.implode( ', ', $enum ).') ' );
        }

        return (string)$param;
    }

    /**
     * @param int|float|string $float
     * @return float
     */
    public static function float($float)
    {
        return (float)$float;
    }

    /**
     * @param $unit
     * @return mixed
     * @throws \ErrorException
     */
    public static function geoUnit($unit)
    {
        if (!in_array( $unit, static::$geoUnits )) {
            throw new \ErrorException( 'Incorrect param "'.$unit.'" for enum('.implode( ', ', static::$geoUnits ).')' );
        }

        return $unit;
    }

    /**
     * @param int|float|string $int
     * @return int
     */
    public static function integer($int)
    {
        return (int)$int;
    }

    /**
     * @param mixed
     * @return int[]
     */
    public static function integers($integers)
    {
        $integers = (array)$integers;

        return array_map( 'static::integer', $integers );
    }

    /**
     * @param string $key
     * @return mixed
     */
    public static function key($key)
    {
        return (string)$key;
    }

    /**
     * @param string|string[] $keys
     * @return array
     */
    public static function keys($keys)
    {
        $keys = (array)$keys;

        return array_map( 'static::key', $keys );
    }

    /**
     * @param $limit
     * @return array
     * @throws \ErrorException
     */
    public static function limit($limit)
    {
        if (is_numeric( $limit )) {
            return [0, (int)$limit];
        }
        if (is_array( $limit ) && isset( $limit['count'] )) {
            return [
              empty( $limit['offset'] ) ? 0 : (int)$limit['offset'],
              (int)$limit['count'],
            ];
        }
        if ($limit && is_string( $limit ) && preg_match( '/^-?\d+\s+-?\d+$/', $limit )) {
            $limit = preg_split( '/\s+/', trim( $limit ), 2 );
        }
        if (is_array( $limit )) {
            if (isset( $limit[0] ) && isset( $limit[1] )) {
                return [(int)$limit[0], (int)$limit[1]];
            }
            if (isset( $limit[0] ) && !isset( $limit[1] )) {
                return [0, (int)$limit[0]];
            }
        }
        throw new \ErrorException("Invalid limit $limit" );
    }

    /**
     * @param $param
     * @return string
     * @throws \ErrorException
     */
    public static function minMax($param)
    {
        $param = trim( $param );
        if (preg_match( static::MIN_MAX_PREG, $param )) {
            return $param;
        }
        throw new \ErrorException("Invalid param $param" );
    }

    /**
     * @param $param
     * @return string
     * @throws \ErrorException
     */
    public static function nxXx($param)
    {
        if ($param === 'NX' || $param === 'XX') {
            return $param;
        }
        if ($param === 'nx' || $param === 'xx') {
            return strtoupper( $param );
        }
        $param = strtoupper( trim( $param ) );
        if ($param === 'NX' || $param === 'XX') {
            return $param;
        }
        throw new \ErrorException("Invalid param $param" );
    }

    /**
     * @param $int
     * @return int
     * @throws \ErrorException
     */
    public static function port($int)
    {
        $int = (int)$int;
        if ($int > 0 && $int <= 65535) {
            return $int;
        }
        throw new \ErrorException( 'Port number must be more than 0 and less than or equal 65535' );
    }

    /**
     * @param $param
     * @return string
     * @throws \ErrorException
     */
    public static function specifyInterval($param)
    {
        $param = trim( $param );
        if (preg_match( static::SPECIFY_INTERVAL_PREG, $param )) {
            return $param;
        }
        throw new \ErrorException("Invalid specify interval $param" );
    }

    /**
     * @param string $string
     * @return string
     */
    public static function string($string)
    {
        return (string)$string;
    }

    /**
     * @param string|string[] $strings
     * @return array
     */
    public static function strings($strings)
    {
        $strings = (array)$strings;

        return array_map( 'static::string', $strings );
    }
}

/* End of file Params.php */
