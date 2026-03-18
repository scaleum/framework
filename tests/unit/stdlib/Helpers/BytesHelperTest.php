<?php
use PHPUnit\Framework\TestCase;
use Scaleum\Stdlib\Helper\BytesHelper;

class BytesHelperTest extends TestCase
{
    public function testBytesAssocWithZeroBytes()
    {
        $this->assertFalse(BytesHelper::bytesAssoc(0));
    }

    public function testBytesAssocWithOneKB()
    {
        $expected = [1, 'kb'];
        $this->assertEquals($expected, BytesHelper::bytesAssoc(1024));
    }

    public function testBytesAssocWithOneMB()
    {
        $expected = [1, 'mb'];
        $this->assertEquals($expected, BytesHelper::bytesAssoc(1048576));
    }

    public function testBytesNumberWithOneKB()
    {
        $this->assertEquals(1024, BytesHelper::bytesNumber('1kb'));
    }

    public function testBytesNumberWithOneMB()
    {
        $this->assertEquals(1048576, BytesHelper::bytesNumber('1mb'));
    }

    public function testBytesNumberWithInvalidString()
    {
        $this->assertEquals(0, BytesHelper::bytesNumber('1xb'));
    }

    public function testBytesStrWithZeroBytes()
    {
        $this->assertFalse(BytesHelper::bytesStr(0));
    }

    public function testBytesStrWithOneKB()
    {
        $expected = '1kb';
        $this->assertEquals($expected, BytesHelper::bytesStr(1024, '%d%s'));
    }

    public function testBytesStrWithOneMB()
    {
        $expected = '1mb';
        $this->assertEquals($expected, BytesHelper::bytesStr(1048576, '%d%s'));
    }

    public function testBytesToBytesToKilobytes()
    {
        $this->assertEquals(1, BytesHelper::bytesTo(1024, 'b', 'kb'));
    }

    public function testBytesToKilobytesToMegabytes()
    {
        $this->assertEquals(1, BytesHelper::bytesTo(1024, 'kb', 'mb'));
    }

    public function testBytesToMegabytesToGigabytes()
    {
        $this->assertEquals(1, BytesHelper::bytesTo(1024, 'mb', 'gb'));
    }
}