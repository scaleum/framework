<?php

declare(strict_types=1);
/**
 * This file is part of Scaleum\Stdlib.
 *
 * (C) 2009-2025 Maxim Kirichenko <kirichenko.maxim@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Scaleum\Stdlib\Helper;

class BytesHelper
{
    /**
     * Converts a size in bytes to a human-readable format with associated units.
     *
     * @param int $size The size in bytes to convert.
     * @return mixed The human-readable size with associated units.
     */
    public static function bytesAssoc(int $size): mixed
    {
        $suffix = ['b', 'kb', 'mb', 'gb', 'tb', 'pb', 'eb', 'zb', 'yb'];

        return $size ? array(round($size / pow(1024, ($i = floor(log($size, 1024)))), 2), $suffix[$i]) : false;
    }

    /**
     * Converts a string representation of a number with a unit of bytes into a numeric value.
     *
     * @param string $str The string representation of the number with a unit of bytes.
     * @return int|float The numeric value of the bytes.
     */
    public static function bytesNumber($str)
    {
        $str    = trim(strtolower($str));
        $num    = str_replace(range('a', 'z'), '', $str);
        $suffix = str_replace(range(0, 9), '', $str);

        $s = [
            'b'  => 'b', 'kb' => 'k',
            'mb' => 'm', 'gb' => 'g',
            'tb' => 't', 'pb' => 'p',
            'eb' => 'e', 'zb' => 'z',
            'yb' => 'y',
        ];

        if (array_key_exists($suffix, $s)) {
            $from = $suffix;
        } else {
            $s = array_flip($s);
            if (array_key_exists($suffix, $s)) {
                $from = $s[$suffix];
            }
        }

        if (!isset($from)) {
            return 0;
        }

        return self::bytesTo((int)$num, $from, 'b');
    }

    /**
     * Converts a size in bytes to a human-readable string representation.
     *
     * @param int $size The size in bytes to convert.
     * @param string $format The format string to use for the conversion. Defaults to '%d%s'.
     * @return mixed The human-readable string representation of the size.
     */
    public static function bytesStr(int $size, string $format = '%d%s'): mixed
    {
        if ($bytes = self::bytesAssoc($size)) {
            return sprintf($format, $bytes[0], $bytes[1]);
        }

        return false;
    }

    /**
     * Converts a size in bytes to a different unit of measurement.
     *
     * @param int $size The size to convert.
     * @param string $from The unit of measurement to convert from. Default is 'b' (bytes).
     * @param string $to The unit of measurement to convert to. Default is 'kb' (kilobytes).
     * @return float The converted size.
     */
    public static function bytesTo(int $size, string $from = 'b', string $to = 'kb'): float
    {
        $sizes = [
            'b'  => $b = 1,
            'kb' => $kb = 1024,
            'mb' => $mb = 1024 * $kb,
            'gb' => $gb = 1024 * $mb,
            'tb' => $tb = 1024 * $gb,
            'pb' => $pb = 1024 * $tb,
            'eb' => $eb = 1024 * $pb,
            'zb' => $zb = 1024 * $eb,
            'yb' => $yb = 1024 * $zb,
        ];

        isset($sizes[strtolower($from)]) || $from = 'b';
        isset($sizes[strtolower($to)]) || $to = 'kb';

        return round(($size * $sizes[$from]) / $sizes[$to], 3);
    }
}
