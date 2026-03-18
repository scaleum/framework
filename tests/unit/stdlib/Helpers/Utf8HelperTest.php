<?php

use PHPUnit\Framework\TestCase;
use Scaleum\Stdlib\Helper\Utf8Helper;

class Utf8HelperTest extends TestCase
{
    public function testClean()
    {
        $this->assertEquals('Hello?', Utf8Helper::clean("Hello\x80"));
        $this->assertEquals('Hello World', Utf8Helper::clean('Hello World'));
        $this->assertEquals('Hello*', Utf8Helper::clean("Hello\x80", '*'));
    }

    public function testCleanUtf8Bom()
    {
        $this->assertEquals('Hello World', Utf8Helper::cleanUtf8Bom("\xef\xbb\xbfHello World"));
        $this->assertEquals('Hello World', Utf8Helper::cleanUtf8Bom('Hello World'));
    }

    public function testGetUtf8Bom()
    {
        $this->assertEquals("\xef\xbb\xbf", Utf8Helper::getUtf8Bom());
    }

    public function testGetUtf8WhiteSpaces()
    {
        $expected = [
            0     => "\x0",
            9     => "\x9",
            10    => "\xa",
            11    => "\xb",
            13    => "\xd",
            32    => "\x20",
            160   => "\xc2\xa0",
            5760  => "\xe1\x9a\x80",
            6158  => "\xe1\xa0\x8e",
            8192  => "\xe2\x80\x80",
            8193  => "\xe2\x80\x81",
            8194  => "\xe2\x80\x82",
            8195  => "\xe2\x80\x83",
            8196  => "\xe2\x80\x84",
            8197  => "\xe2\x80\x85",
            8198  => "\xe2\x80\x86",
            8199  => "\xe2\x80\x87",
            8200  => "\xe2\x80\x88",
            8201  => "\xe2\x80\x89",
            8202  => "\xe2\x80\x8a",
            8232  => "\xe2\x80\xa8",
            8233  => "\xe2\x80\xa9",
            8239  => "\xe2\x80\xaf",
            8287  => "\xe2\x81\x9f",
            12288 => "\xe3\x80\x80"
        ];
        $this->assertEquals($expected, Utf8Helper::getUtf8WhiteSpaces());
    }

    public function testIsUtf8()
    {
        $this->assertTrue(Utf8Helper::isUtf8('Hello World'));
        $this->assertFalse(Utf8Helper::isUtf8("Hello\x80World"));
    }

    public function testIsUtf8Bom()
    {
        $this->assertTrue(Utf8Helper::isUtf8Bom("\xef\xbb\xbf"));
        $this->assertFalse(Utf8Helper::isUtf8Bom('Hello World'));
    }
}