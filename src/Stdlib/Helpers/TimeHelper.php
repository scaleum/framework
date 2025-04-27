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
        $endOfDay = $date . ' 23:59:59';
        return strtotime($endOfDay);
    }
}