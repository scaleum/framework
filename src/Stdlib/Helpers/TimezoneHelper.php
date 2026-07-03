<?php

declare(strict_types=1);
/**
 * This file is part of Scaleum Framework.
 *
 * (C) 2009-2025 Maxim Kirichenko <kirichenko.maxim@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Scaleum\Stdlib\Helpers;

use DateTimeImmutable;
use DateTimeInterface;
use DateTimeZone;
use Scaleum\Stdlib\Exceptions\EInvalidArgumentException;
use Throwable;

/**
 * Timezone helper for fixed UTC offsets and regional IANA timezones.
 *
 * Fixed offsets are stored and returned in seconds. Regional timezone offsets
 * are resolved for a concrete timestamp through DateTimeZone, so DST rules are
 * applied by PHP timezone data.
 */
class TimezoneHelper
{
    protected const TIMEZONE_ALIASES = [
        'Europe/Kyiv' => 'Europe/Kiev',
    ];

    /**
     * Fixed UTC offset zones.
     *
     * The `offset` value is always stored in seconds.
     *
     * Example: TimezoneHelper::$zones['UTC+575']['offset'] === 20700
     *
     * @var array<string, array{offset: int, friendly: string}>
     */
    public static array $zones = [
        'UTC-12'   => ['offset' => -43200, 'friendly' => '[UTC -12:00] Enitwetok, Kwajalien'],
        'UTC-11'   => ['offset' => -39600, 'friendly' => '[UTC -11:00] Nome, Midway Island, Samoa'],
        'UTC-10'   => ['offset' => -36000, 'friendly' => '[UTC -10:00] Hawaii'],
        'UTC-95'   => ['offset' => -34200, 'friendly' => '[UTC -09:30] Marquesas Time'],
        'UTC-9'    => ['offset' => -32400, 'friendly' => '[UTC -09:00] Alaska'],
        'UTC-8'    => ['offset' => -28800, 'friendly' => '[UTC -08:00] Pacific Time'],
        'UTC-7'    => ['offset' => -25200, 'friendly' => '[UTC -07:00] Mountain Time'],
        'UTC-6'    => ['offset' => -21600, 'friendly' => '[UTC -06:00] Central Time, Mexico City'],
        'UTC-5'    => ['offset' => -18000, 'friendly' => '[UTC -05:00] Eastern Time, Bogota, Lima, Quito'],
        'UTC-45'   => ['offset' => -16200, 'friendly' => '[UTC -04:30] Venezuelan'],
        'UTC-4'    => ['offset' => -14400, 'friendly' => '[UTC -04:00] Atlantic Time, Caracas, La Paz'],
        'UTC-35'   => ['offset' => -12600, 'friendly' => '[UTC -03:30] Newfoundland'],
        'UTC-3'    => ['offset' => -10800, 'friendly' => '[UTC -03:00] Brazil, Buenos Aires, Georgetown, Falkland Is.'],
        'UTC-2'    => ['offset' => -7200, 'friendly' => '[UTC -02:00] Mid-Atlantic, Ascention Is., St Helena'],
        'UTC-1'    => ['offset' => -3600, 'friendly' => '[UTC -01:00] Azores, Cape Verde Islands'],
        'UTC+0'    => ['offset' => 0, 'friendly' => '[UTC +00:00] Casablanca, Dublin, Edinburgh, London'],
        'UTC+1'    => ['offset' => 3600, 'friendly' => '[UTC +01:00] Berlin, Brussels, Copenhagen, Madrid, Paris, Rome'],
        'UTC+2'    => ['offset' => 7200, 'friendly' => '[UTC +02:00] Kaliningrad, South Africa, Warsaw'],
        'UTC+3'    => ['offset' => 10800, 'friendly' => '[UTC+03:00] Helsinki, Riga, Sofia, Tallinn, Vilnius'],
        'UTC+35'   => ['offset' => 12600, 'friendly' => '[UTC +03:30] Tehran'],
        'UTC+4'    => ['offset' => 14400, 'friendly' => '[UTC +04:00] Adu Dhabi, Baku, Muscat, Tbilisi'],
        'UTC+45'   => ['offset' => 16200, 'friendly' => '[UTC +04:30] Kabul'],
        'UTC+5'    => ['offset' => 18000, 'friendly' => '[UTC +05:00] Islamabad, Karachi, Tashkent'],
        'UTC+55'   => ['offset' => 19800, 'friendly' => '[UTC +05:30] Bombay, Calcutta, Madras, New Delhi'],
        'UTC+575'  => ['offset' => 20700, 'friendly' => '[UTC +05:45] Nepal'],
        'UTC+6'    => ['offset' => 21600, 'friendly' => '[UTC +06:00] Almaty, Colomba, Dhaka'],
        'UTC+65'   => ['offset' => 23400, 'friendly' => '[UTC +06:30] Myanmar, Cocos Islands'],
        'UTC+7'    => ['offset' => 25200, 'friendly' => '[UTC +07:00] Bangkok, Hanoi, Jakarta'],
        'UTC+8'    => ['offset' => 28800, 'friendly' => '[UTC +08:00] Beijing, Hong Kong, Perth, Singapore, Taipei'],
        'UTC+875'  => ['offset' => 31500, 'friendly' => '[UTC +08:45] Australia Border Village, Caiguna, Eucla'],
        'UTC+9'    => ['offset' => 32400, 'friendly' => '[UTC +09:00] Osaka, Sapporo, Seoul, Tokyo, Yakutsk'],
        'UTC+95'   => ['offset' => 34200, 'friendly' => '[UTC +09:30] Adelaide, Darwin'],
        'UTC+10'   => ['offset' => 36000, 'friendly' => '[UTC +10:00] Melbourne, Papua New Guinea, Sydney, Vladivostok'],
        'UTC+105'  => ['offset' => 37800, 'friendly' => '[UTC +10:30] Central Daylight Time'],
        'UTC+11'   => ['offset' => 39600, 'friendly' => '[UTC +11:00] Magadan, New Caledonia, Solomon Islands'],
        'UTC+115'  => ['offset' => 41400, 'friendly' => '[UTC +11:30] Norfolk'],
        'UTC+12'   => ['offset' => 43200, 'friendly' => '[UTC +12:00] Auckland, Wellington, Fiji, Marshall Island'],
        'UTC+1275' => ['offset' => 45900, 'friendly' => '[UTC +12:45] Chatham Island'],
        'UTC+13'   => ['offset' => 46800, 'friendly' => '[UTC +13:00] New Zealand, Phoenix Island, West Samoa'],
        'UTC+14'   => ['offset' => 50400, 'friendly' => '[UTC +14:00] Line Islands, Tokelau'],
    ];

    /**
     * Converts a local timestamp representation to the matching UTC timestamp.
     *
     * For fixed offsets this subtracts the offset seconds. For regional IANA
     * zones the input timestamp is treated as a wall-clock local date/time.
     *
     * Example: TimezoneHelper::UTCFromLocal(1783080000, 'Europe/Kyiv') === 1783069200
     *
     * @throws EInvalidArgumentException When timezone is invalid.
     */
    public static function UTCFromLocal(int $timestamp, string $timezone = 'UTC+0'): int
    {
        return self::localToUtcTimestamp($timestamp, $timezone);
    }

    /**
     * Converts a UTC timestamp to a local timestamp representation.
     *
     * The returned integer keeps the same timestamp format used by the legacy
     * helper: UTC timestamp plus the timezone offset seconds.
     *
     * Example: TimezoneHelper::UTCToLocal(1783069200, 'Europe/Kyiv') === 1783080000
     *
     * @throws EInvalidArgumentException When timezone is invalid.
     */
    public static function UTCToLocal(int $timestamp, string $timezone = 'UTC+0'): int
    {
        return self::utcToLocalTimestamp($timestamp, $timezone);
    }

    /**
     * Converts a local timestamp representation to UTC.
     *
     * Use this when an integer timestamp stores local wall-clock components,
     * for example `2026-07-03 12:00:00` encoded with gmmktime().
     *
     * Example: TimezoneHelper::localToUtcTimestamp(1783080000, 'Europe/Kyiv') === 1783069200
     *
     * @throws EInvalidArgumentException When timezone is invalid.
     */
    public static function localToUtcTimestamp(int $timestamp, string $timezone = 'UTC+0'): int
    {
        if (self::isFixedOffset($timezone)) {
            return $timestamp - self::timezoneOffset($timezone, $timestamp);
        }

        $localTime = DateTimeImmutable::createFromFormat(
            '!Y-m-d H:i:s',
            gmdate('Y-m-d H:i:s', $timestamp),
            self::dateTimeZone($timezone)
        );

        if (! $localTime instanceof DateTimeImmutable) {
            throw new EInvalidArgumentException(sprintf('Invalid local timestamp for timezone `%s`.', $timezone));
        }

        return $localTime->setTimezone(new DateTimeZone('UTC'))->getTimestamp();
    }

    /**
     * Converts a UTC timestamp to a local timestamp representation.
     *
     * For regional IANA zones, the offset is resolved for the given UTC moment,
     * so DST transitions are respected.
     *
     * Example: TimezoneHelper::utcToLocalTimestamp(1783069200, 'Europe/Kyiv') === 1783080000
     *
     * @throws EInvalidArgumentException When timezone is invalid.
     */
    public static function utcToLocalTimestamp(int $timestamp, string $timezone = 'UTC+0'): int
    {
        return $timestamp + self::timezoneOffset($timezone, $timestamp);
    }

    /**
     * Creates a DateTimeImmutable for a UTC timestamp in the requested timezone.
     *
     * Accepts both fixed offsets and regional IANA timezone identifiers.
     *
     * Example: TimezoneHelper::toTimezone(1767225600, 'UTC+575')->format('Y-m-d H:i:s') === '2026-01-01 05:45:00'
     *
     * @throws EInvalidArgumentException When timezone is invalid.
     */
    public static function toTimezone(int $timestamp, string $timezone): DateTimeImmutable
    {
        return (new DateTimeImmutable('@' . $timestamp))->setTimezone(self::dateTimeZone($timezone));
    }

    /**
     * Interprets a local date/time in the given timezone and returns it in UTC.
     *
     * The input can be a parseable date/time string or any DateTimeInterface.
     *
     * Example: TimezoneHelper::fromLocal('2026-07-03 12:00:00', 'Europe/Kyiv')->format('Y-m-d H:i:s') === '2026-07-03 09:00:00'
     *
     * @throws EInvalidArgumentException When timezone is invalid.
     */
    public static function fromLocal(string | DateTimeInterface $localTime, string $timezone): DateTimeImmutable
    {
        $zone = self::dateTimeZone($timezone);

        if ($localTime instanceof DateTimeInterface) {
            $localTime = $localTime->format('Y-m-d H:i:s.u');
        }

        return (new DateTimeImmutable($localTime, $zone))->setTimezone(new DateTimeZone('UTC'));
    }

    /**
     * Returns timezone metadata for one timezone or the full fixed-offset list.
     *
     * Fixed zone metadata comes from self::$zones. Regional IANA zones return a
     * generated pair with current offset seconds and the timezone id as label.
     *
     * Example: TimezoneHelper::timezoneAssoc('UTC+575')['offset'] === 20700
     * Example: array_key_exists('UTC+0', TimezoneHelper::timezoneAssoc()) === true
     *
     * @return array<string, mixed>
     *
     * @throws EInvalidArgumentException When timezone is invalid.
     */
    public static function timezoneAssoc(string $tz = ''): array
    {
        if ($tz === '') {
            return self::$zones;
        }

        if (isset(self::$zones[$tz])) {
            return self::$zones[$tz];
        }

        if (self::isValid($tz)) {
            return [
                'offset'   => self::timezoneOffset($tz),
                'friendly' => $tz,
            ];
        }

        throw new EInvalidArgumentException(sprintf('Invalid timezone `%s`.', $tz));
    }

    /**
     * Returns a value-label map for UI select, radio or checkbox controls.
     *
     * Without arguments it returns all predefined fixed-offset zones. Pass a
     * timezone list to build a smaller map or include regional IANA zones.
     *
     * Example: TimezoneHelper::timezoneOptions()['UTC+575'] === '[UTC +05:45] Nepal'
     * Example: TimezoneHelper::timezoneOptions(['UTC+0', 'Europe/Kyiv']) === ['UTC+0' => '[UTC +00:00] Casablanca, Dublin, Edinburgh, London', 'Europe/Kyiv' => 'Europe/Kyiv']
     *
     * @param array<int, string> $timezones
     * @return array<string, string>
     *
     * @throws EInvalidArgumentException When timezone is invalid.
     */
    public static function timezoneOptions(array $timezones = []): array
    {
        $zones   = $timezones === [] ? array_keys(self::$zones) : $timezones;
        $options = [];

        foreach ($zones as $timezone) {
            $options[$timezone] = self::timezoneAssoc($timezone)['friendly'];
        }

        return $options;
    }

    /**
     * Returns timezone offset in seconds.
     *
     * Fixed offsets ignore the timestamp. Regional IANA zones use the timestamp
     * to resolve DST-sensitive offsets; current time is used when omitted.
     *
     * Example: TimezoneHelper::timezoneOffset('UTC+575') === 20700
     * Example: TimezoneHelper::timezoneOffset('Europe/Kyiv', 1783069200) === 10800
     *
     * @throws EInvalidArgumentException When timezone is invalid.
     */
    public static function timezoneOffset(string $tz, ?int $timestamp = null): int
    {
        $fixedOffset = self::parseFixedOffset($tz);
        if ($fixedOffset !== null) {
            return $fixedOffset;
        }

        return self::dateTimeZone($tz)->getOffset(new DateTimeImmutable('@' . ($timestamp ?? time())));
    }

    /**
     * Checks whether a timezone string is a fixed UTC offset.
     *
     * Supports predefined keys, raw offsets, UTC, GMT and Z.
     *
     * Example: TimezoneHelper::isFixedOffset('UTC+575') === true
     * Example: TimezoneHelper::isFixedOffset('Europe/Kyiv') === false
     */
    public static function isFixedOffset(string $timezone): bool
    {
        return self::parseFixedOffset($timezone) !== null;
    }

    /**
     * Checks whether a timezone string is a regional IANA timezone.
     *
     * Fixed offsets are deliberately excluded even when DateTimeZone can parse
     * an equivalent `+HH:MM` value.
     *
     * Example: TimezoneHelper::isRegionTimezone('Europe/Kyiv') === true
     * Example: TimezoneHelper::isRegionTimezone('UTC+575') === false
     */
    public static function isRegionTimezone(string $timezone): bool
    {
        if (self::isFixedOffset($timezone)) {
            return false;
        }

        try {
            new DateTimeZone(self::normalizeTimezone($timezone));

            return true;
        } catch (Throwable) {
            return false;
        }
    }

    /**
     * Checks whether a timezone string is supported by this helper.
     *
     * A supported timezone is either a fixed UTC offset or a regional IANA
     * timezone accepted by PHP timezone data.
     *
     * Example: TimezoneHelper::isValid('UTC+575') === true
     * Example: TimezoneHelper::isValid('No/Such_Zone') === false
     */
    public static function isValid(string $timezone): bool
    {
        return self::isFixedOffset($timezone) || self::isRegionTimezone($timezone);
    }

    protected static function dateTimeZone(string $timezone): DateTimeZone
    {
        $fixedOffset = self::parseFixedOffset($timezone);
        if ($fixedOffset !== null) {
            return new DateTimeZone(self::formatOffset($fixedOffset));
        }

        try {
            return new DateTimeZone(self::normalizeTimezone($timezone));
        } catch (Throwable $exception) {
            throw new EInvalidArgumentException(sprintf('Invalid timezone `%s`.', $timezone), previous: $exception);
        }
    }

    protected static function normalizeTimezone(string $timezone): string
    {
        return self::TIMEZONE_ALIASES[$timezone] ?? $timezone;
    }

    protected static function parseFixedOffset(string $timezone): ?int
    {
        if (isset(self::$zones[$timezone])) {
            return self::$zones[$timezone]['offset'];
        }

        if (strtoupper($timezone) === 'UTC' || strtoupper($timezone) === 'GMT' || $timezone === 'Z') {
            return 0;
        }

        if (! preg_match('/^(?:UTC|GMT)?([+-])(\d{1,2})(?::?(\d{2}))?$/i', $timezone, $matches)) {
            return null;
        }

        $hours   = (int) $matches[2];
        $minutes = isset($matches[3]) && $matches[3] !== '' ? (int) $matches[3] : 0;

        if ($hours > 14 || $minutes > 59 || ($hours === 14 && $minutes > 0)) {
            return null;
        }

        $offset = ($hours * 3600) + ($minutes * 60);

        return $matches[1] === '-' ? -$offset : $offset;
    }

    protected static function formatOffset(int $offset): string
    {
        $sign    = $offset < 0 ? '-' : '+';
        $offset  = abs($offset);
        $hours   = intdiv($offset, 3600);
        $minutes = intdiv($offset % 3600, 60);

        return sprintf('%s%02d:%02d', $sign, $hours, $minutes);
    }
}
