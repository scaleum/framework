<?php
declare (strict_types = 1);
/**
 * This file is part of Scaleum\Storages\Redis.
 *
 * (C) 2009-2025 Maxim Kirichenko <kirichenko.maxim@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Scaleum\Storages\Redis;

class Response {
    public const TYPE_ASSOC_ARRAY = 1;
    public const TYPE_INTEGER     = 2;
    public const TYPE_TIME        = 3;
    public const TYPE_INFO        = 4;
    public const TYPE_GEO_ARRAY   = 5;
    public const TYPE_CLIENT_LIST = 6;

    public static function parse($type, $response) {
        return match ($type) {
            self::TYPE_ASSOC_ARRAY => self::parseAssocArray($response),
            self::TYPE_INTEGER => self::parseInteger($response),
            self::TYPE_TIME => self::parseTime($response),
            self::TYPE_INFO => self::parseInfo($response),
            self::TYPE_GEO_ARRAY => self::parseGeoArray($response),
            self::TYPE_CLIENT_LIST => self::parseClientList($response),
            default => $response,
        };
    }

    protected static function parseAssocArray($response) {
        if (! is_array($response)) {
            return $response;
        }
        $array = [];
        for ($i = 0, $count = count($response); $i < $count; $i += 2) {
            $array[$response[$i]] = $response[$i + 1];
        }

        return $array;
    }

    protected static function parseClientList($response) {
        if (! is_string($response)) {
            return $response;
        }
        $array = [];
        foreach (explode("\n", trim($response)) as $client) {
            $c = [];
            foreach (explode(' ', trim($client)) as $param) {
                $args = explode('=', $param, 2);
                if (isset($args[0], $args[1]) && ($key = trim($args[0]))) {
                    $c[$key] = trim($args[1]);
                }
            }
            if ($c) {
                $array[] = $c;
            }
        }

        return $array;
    }

    protected static function parseGeoArray($response) {
        if (! is_array($response)) {
            return $response;
        }
        $array = [];
        for ($i = 0, $count = count($response); $i < $count; $i += 1) {
            $array[array_shift($response[$i])] = $response[$i];
        }

        return $array;
    }

    protected static function parseInfo($response) {
        if (! $response) {
            return $response;
        }
        $response = trim((string) $response);
        $result   = [];
        $link     = &$result;
        foreach (explode("\n", $response) as $line) {
            $line = trim($line);
            if (! $line) {
                $link = &$result;
                continue;
            } elseif ($line[0] === '#') {
                $section          = trim($line, '# ');
                $result[$section] = [];
                $link             = &$result[$section];
                continue;
            }
            list($key, $value) = explode(':', $line, 2);
            $link[trim($key)]  = trim($value);
        }
        if (count($result) === 1 && isset($section)) {
            return $result[$section];
        }

        return $result;
    }

    protected static function parseInteger($response) {
        return (int) $response;
    }

    protected static function parseTime(array $response) {
        if (is_array($response) && count($response) === 2) {
            if (($len = strlen($response[1])) < 6) {
                $response[1] = str_repeat('0', 6 - $len) . $response[1];
            }

            return implode('.', $response);
        }

        return $response;
    }
}

/* End of file Response.php */
