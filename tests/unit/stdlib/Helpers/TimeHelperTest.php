<?php
use PHPUnit\Framework\TestCase;
use Scaleum\Stdlib\Exceptions\EInvalidArgumentException;
use Scaleum\Stdlib\Helpers\TimeHelper;

class TimeHelperTest extends TestCase
{
    public function testGetTimestampDiffInSecondsAbsolute()
    {
        $from = strtotime('2026-01-01 00:00:00');
        $to = strtotime('2026-01-01 00:01:05');

        $this->assertEquals(65, TimeHelper::getTimestampDiff($from, $to, 'second'));
    }

    public function testGetTimestampDiffInHoursSigned()
    {
        $from = strtotime('2026-01-01 12:00:00');
        $to = strtotime('2026-01-01 10:00:00');

        $this->assertEquals(-2, TimeHelper::getTimestampDiff($from, $to, 'hour', false));
    }

    public function testGetTimestampDiffInMonths()
    {
        $from = strtotime('2026-01-15 00:00:00');
        $to = strtotime('2026-04-15 00:00:00');

        $this->assertEquals(3, TimeHelper::getTimestampDiff($from, $to, 'month'));
    }

    public function testGetTimestampDiffInWeeks()
    {
        $from = strtotime('2026-01-01 00:00:00');
        $to = strtotime('2026-01-22 00:00:00');

        $this->assertEquals(3, TimeHelper::getTimestampDiff($from, $to, 'week'));
    }

    public function testGetTimestampDiffThrowsForUnsupportedUnit()
    {
        $this->expectException(EInvalidArgumentException::class);

        TimeHelper::getTimestampDiff(time(), time() + 10, 'quarter');
    }
}
