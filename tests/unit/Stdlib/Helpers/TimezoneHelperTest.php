<?php
use PHPUnit\Framework\TestCase;
use Scaleum\Stdlib\Exceptions\EInvalidArgumentException;
use Scaleum\Stdlib\Helpers\TimezoneHelper;

class TimezoneHelperTest extends TestCase
{
    public function testTimezoneOffsetReturnsSecondsForFixedZone()
    {
        $this->assertSame(20700, TimezoneHelper::timezoneOffset('UTC+575'));
        $this->assertSame(-12600, TimezoneHelper::timezoneOffset('UTC-35'));
    }

    public function testTimezoneOffsetParsesRawFixedOffset()
    {
        $this->assertSame(12600, TimezoneHelper::timezoneOffset('+03:30'));
        $this->assertSame(-18000, TimezoneHelper::timezoneOffset('UTC-05:00'));
    }

    public function testTimezoneOffsetUsesRegionRulesForTimestamp()
    {
        $winter = gmmktime(12, 0, 0, 1, 10, 2026);
        $summer = gmmktime(12, 0, 0, 7, 10, 2026);

        $this->assertSame(7200, TimezoneHelper::timezoneOffset('Europe/Kyiv', $winter));
        $this->assertSame(10800, TimezoneHelper::timezoneOffset('Europe/Kyiv', $summer));
    }

    public function testUtcToLocalTimestampWithRegionTimezone()
    {
        $utcTimestamp = gmmktime(9, 0, 0, 7, 3, 2026);
        $localTimestamp = gmmktime(12, 0, 0, 7, 3, 2026);

        $this->assertSame($localTimestamp, TimezoneHelper::UTCToLocal($utcTimestamp, 'Europe/Kyiv'));
    }

    public function testLocalToUtcTimestampWithRegionTimezone()
    {
        $localTimestamp = gmmktime(12, 0, 0, 7, 3, 2026);
        $utcTimestamp = gmmktime(9, 0, 0, 7, 3, 2026);

        $this->assertSame($utcTimestamp, TimezoneHelper::UTCFromLocal($localTimestamp, 'Europe/Kyiv'));
    }

    public function testToTimezoneWithFixedOffset()
    {
        $datetime = TimezoneHelper::toTimezone(gmmktime(0, 0, 0, 1, 1, 2026), 'UTC+575');

        $this->assertSame('2026-01-01 05:45:00', $datetime->format('Y-m-d H:i:s'));
    }

    public function testFromLocalWithRegionTimezone()
    {
        $datetime = TimezoneHelper::fromLocal('2026-07-03 12:00:00', 'Europe/Kyiv');

        $this->assertSame('2026-07-03 09:00:00', $datetime->format('Y-m-d H:i:s'));
        $this->assertSame('UTC', $datetime->getTimezone()->getName());
    }

    public function testTimezoneAssocReturnsFixedZones()
    {
        $zones = TimezoneHelper::timezoneAssoc();

        $this->assertArrayHasKey('UTC+575', $zones);
        $this->assertSame(20700, $zones['UTC+575']['offset']);
    }

    public function testTimezoneOptionsReturnsFixedZoneLabels()
    {
        $options = TimezoneHelper::timezoneOptions();

        $this->assertArrayHasKey('UTC+575', $options);
        $this->assertSame('[UTC +05:45] Nepal', $options['UTC+575']);
    }

    public function testTimezoneOptionsAcceptsCustomTimezoneList()
    {
        $expected = [
            'UTC+0' => '[UTC +00:00] Casablanca, Dublin, Edinburgh, London',
            'Europe/Kyiv' => 'Europe/Kyiv',
        ];

        $this->assertSame($expected, TimezoneHelper::timezoneOptions(['UTC+0', 'Europe/Kyiv']));
    }

    public function testTimezoneAssocThrowsForInvalidTimezone()
    {
        $this->expectException(EInvalidArgumentException::class);

        TimezoneHelper::timezoneAssoc('No/Such_Zone');
    }
}
