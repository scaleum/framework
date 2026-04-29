<?php
declare (strict_types = 1);
/**
 * This file is part of Scaleum Framework.
 *
 * (C) 2009-2025 Maxim Kirichenko <kirichenko.maxim@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Scaleum\Stdlib\Helpers;

use DateTime;
use DateTimeImmutable;
use Scaleum\Stdlib\Exceptions\EInvalidArgumentException;

/**
 * Representation of time in seconds
 */
class TimeHelper {
    public const Second = 1;
    public const Minute = self::Second * 60;
    public const Hour   = self::Minute * 60;
    public const Day    = self::Hour * 24;

    /**
     * Get the timestamp representing the end of the day.
     *
     * @param int $offset Optional offset in seconds to adjust the end of the day timestamp.
     * @return int The timestamp representing the end of the day.
     */
    public static function getEndOfDayTimestamp(int $offset = 0) {
        $date     = date('Y-m-d', time() + $offset);
        $endOfDay = "$date 23:59:59";
        return strtotime($endOfDay);
    }

    /**
     * Get the timestamp for the beginning of the day with an optional offset.
     *
     * This method returns the timestamp for the start of the current day,
     * adjusted by the specified offset in days.
     *
     * @param int $offset The number of days to offset from the current day. Default is 0.
     * @return int The timestamp for the beginning of the day with the specified offset.
     */
    public static function getBeginOfDayTimestamp(int $offset = 0) {
        $date       = date('Y-m-d', time() + $offset);
        $beginOfDay = "$date 00:00:01";
        return strtotime($beginOfDay);
    }

    /**
     * Get the Unix timestamp with a specified interval offset.
     *
     * @param int $unixtime The original Unix timestamp.
     * @param string $interval The interval to offset the timestamp by (e.g., '+1 day', '-2 hours').
     * @return int The Unix timestamp after applying the interval offset.
     */
    public static function getUnixtimeWithOffset($unixtime, string $interval) {
        $date = new DateTime("@$unixtime");
        $date->modify($interval);
        return $date->getTimestamp();
    }

    /**
     * Get difference between two timestamps in the specified time unit.
     *
     * Supported units: year, month, week, day, hour, minute, second.
     * Short aliases are supported as well: y, mo, w, d, h, m, s.
     *
     * @param int $fromTimestamp Start timestamp.
     * @param int $toTimestamp End timestamp.
     * @param string $unit Target unit for difference value.
     * @param bool $absolute If true (default), return absolute difference.
     * @return int Difference in complete units.
     * @throws EInvalidArgumentException When unit is not supported.
     */
    public static function getTimestampDiff(int $fromTimestamp, int $toTimestamp, string $unit = 'second', bool $absolute = true): int {
        $normalizedUnit = strtolower(trim($unit));
        $unitMap        = [
            'y'       => 'year',
            'year'    => 'year',
            'years'   => 'year',
            'mo'      => 'month',
            'month'   => 'month',
            'months'  => 'month',
            'w'       => 'week',
            'week'    => 'week',
            'weeks'   => 'week',
            'd'       => 'day',
            'day'     => 'day',
            'days'    => 'day',
            'h'       => 'hour',
            'hour'    => 'hour',
            'hours'   => 'hour',
            'm'       => 'minute',
            'minute'  => 'minute',
            'minutes' => 'minute',
            's'       => 'second',
            'second'  => 'second',
            'seconds' => 'second',
        ];

        if (! isset($unitMap[$normalizedUnit])) {
            throw new EInvalidArgumentException(sprintf('Unsupported time unit "%s"', $unit));
        }

        $unit     = $unitMap[$normalizedUnit];
        $fromDate = (new DateTimeImmutable())->setTimestamp($fromTimestamp);
        $toDate   = (new DateTimeImmutable())->setTimestamp($toTimestamp);
        $interval = $fromDate->diff($toDate, $absolute);

        switch ($unit) {
        case 'year':
            $value = $interval->y;
            break;
        case 'month':
            $value = ($interval->y * 12) + $interval->m;
            break;
        case 'week':
            $value = intdiv((int) $interval->days, 7);
            break;
        case 'day':
            $value = (int) $interval->days;
            break;
        case 'hour':
            $value = ((int) $interval->days * 24) + $interval->h;
            break;
        case 'minute':
            $value = (((int) $interval->days * 24 + $interval->h) * 60) + $interval->i;
            break;
        case 'second':
            $value = ((((int) $interval->days * 24 + $interval->h) * 60 + $interval->i) * 60) + $interval->s;
            break;
        default:
            throw new EInvalidArgumentException(sprintf('Unsupported time unit "%s"', $unit));
        }

        if ($absolute) {
            return (int) $value;
        }

        return $interval->invert ? -((int) $value) : (int) $value;
    }
}