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

class TimezoneHelper {
    public static $zones = [
        'UTC-12'   => ['offset' => -12, 'friendly' => '[UTC -12:00] Enitwetok, Kwajalien'],
        'UTC-11'   => ['offset' => -11, 'friendly' => '[UTC -11:00] Nome, Midway Island, Samoa'],
        'UTC-10'   => ['offset' => -10, 'friendly' => '[UTC -10:00] Hawaii'],
        'UTC-95'   => ['offset' => -9.5, 'friendly' => '[UTC -09:30] Marquesas Time'],
        'UTC-9'    => ['offset' => -9, 'friendly' => '[UTC -09:00] Alaska'],
        'UTC-8'    => ['offset' => -8, 'friendly' => '[UTC -08:00] Pacific Time'],
        'UTC-7'    => ['offset' => -7, 'friendly' => '[UTC -07:00] Mountain Time'],
        'UTC-6'    => ['offset' => -6, 'friendly' => '[UTC -06:00] Central Time, Mexico City'],
        'UTC-5'    => ['offset' => -5, 'friendly' => '[UTC -05:00] Eastern Time, Bogota, Lima, Quito'],
        'UTC-45'   => ['offset' => -4.5, 'friendly' => '[UTC -04:30] Venezuelan'],
        'UTC-4'    => ['offset' => -4, 'friendly' => '[UTC -04:00] Atlantic Time, Caracas, La Paz'],
        'UTC-35'   => ['offset' => -3.5, 'friendly' => '[UTC -03:30] Newfoundland'],
        'UTC-3'    => ['offset' => -3, 'friendly' => '[UTC -03:00] Brazil, Buenos Aires, Georgetown, Falkland Is.'],
        'UTC-2'    => ['offset' => -2, 'friendly' => '[UTC -02:00] Mid-Atlantic, Ascention Is., St Helena'],
        'UTC-1'    => ['offset' => -1, 'friendly' => '[UTC -01:00] Azores, Cape Verde Islands'],
        'UTC+0'    => ['offset' => 0, 'friendly' => '[UTC +00:00] Casablanca, Dublin, Edinburgh, London'],
        'UTC+1'    => ['offset' => +1, 'friendly' => '[UTC +01:00] Berlin, Brussels, Copenhagen, Madrid, Paris, Rome'],
        'UTC+2'    => ['offset' => +2, 'friendly' => '[UTC +02:00] Kaliningrad, South Africa, Warsaw'],
        'UTC+3'    => ['offset' => +3, 'friendly' => '[UTC+03:00] Helsinki, Riga, Sofia, Tallinn, Vilnius'],
        'UTC+35'   => ['offset' => +3.5, 'friendly' => '[UTC +03:30] Tehran'],
        'UTC+4'    => ['offset' => +4, 'friendly' => '[UTC +04:00] Adu Dhabi, Baku, Muscat, Tbilisi'],
        'UTC+45'   => ['offset' => +4.5, 'friendly' => '[UTC +04:30] Kabul'],
        'UTC+5'    => ['offset' => +5, 'friendly' => '[UTC +05:00] Islamabad, Karachi, Tashkent'],
        'UTC+55'   => ['offset' => +5.5, 'friendly' => '[UTC +05:30] Bombay, Calcutta, Madras, New Delhi'],
        'UTC+575'  => ['offset' => +5.75, 'friendly' => '[UTC +05:45] Nepal'],
        'UTC+6'    => ['offset' => +6, 'friendly' => '[UTC +06:00] Almaty, Colomba, Dhaka'],
        'UTC+65'   => ['offset' => +6.5, 'friendly' => '[UTC +06:30] Myanmar, Cocos Islands'],
        'UTC+7'    => ['offset' => +7, 'friendly' => '[UTC +07:00] Bangkok, Hanoi, Jakarta'],
        'UTC+8'    => ['offset' => +8, 'friendly' => '[UTC +08:00] Beijing, Hong Kong, Perth, Singapore, Taipei'],
        'UTC+875'  => ['offset' => +8.75, 'friendly' => '[UTC +08:45] Australia Border Village, Caiguna, Eucla'],
        'UTC+9'    => ['offset' => +9, 'friendly' => '[UTC +09:00] Osaka, Sapporo, Seoul, Tokyo, Yakutsk'],
        'UTC+95'   => ['offset' => +9.5, 'friendly' => '[UTC +09:30] Adelaide, Darwin'],
        'UTC+10'   => ['offset' => +10, 'friendly' => '[UTC +10:00] Melbourne, Papua New Guinea, Sydney, Vladivostok'],
        'UTC+105'  => ['offset' => +10.5, 'friendly' => '[UTC +10:30] Central Daylight Time'],
        'UTC+11'   => ['offset' => +11, 'friendly' => '[UTC +11:00] Magadan, New Caledonia, Solomon Islands'],
        'UTC+115'  => ['offset' => +11.5, 'friendly' => '[UTC +11:30] Norfolk'],
        'UTC+12'   => ['offset' => +12, 'friendly' => '[UTC +12:00] Auckland, Wellington, Fiji, Marshall Island'],
        'UTC+1275' => ['offset' => +12.75, 'friendly' => '[UTC +12:45] Chatham Island'],
        'UTC+13'   => ['offset' => +13, 'friendly' => '[UTC +13:00] New Zealand, Phoenix Island, West Samoa'],
        'UTC+14'   => ['offset' => +14, 'friendly' => '[UTC +14:00] Line Islands, Tokelau'],
    ];

    public static function UTCFromLocal($timestamp, $timezone = 'UTC+0') {
        $timestamp -= self::timezoneOffset($timezone) * 3600;

        return $timestamp;
    }

    public static function UTCToLocal($timestamp, $timezone = 'UTC+0') {
        $timestamp += self::timezoneOffset($timezone) * 3600;

        return $timestamp;
    }

    public static function timezoneAssoc($tz = '') {

        if ($tz == '') {
            return self::$zones;
        }

        return (! isset(self::$zones[$tz])) ? self::$zones['UTC+0'] : self::$zones[$tz];
    }

    public static function timezoneOffset($tz) {
        $zone = self::timezoneAssoc($tz);

        return $zone['offset'];
    }
}