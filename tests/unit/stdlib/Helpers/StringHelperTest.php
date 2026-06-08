<?php

use PHPUnit\Framework\TestCase;
use Scaleum\Stdlib\Helpers\StringHelper;

class StringHelperTest extends TestCase
{
    public function testIsSerializable()
    {
        $this->assertTrue(StringHelper::isSerializable(['key' => 'value']));
        $this->assertTrue(StringHelper::isSerializable(fopen('php://memory', 'r')));
    }

    public function testIsAscii()
    {
        $this->assertTrue(StringHelper::isAscii('Hello World'));
        $this->assertFalse(StringHelper::isAscii('Héllo Wörld'));
    }

    public function testCleanInvisibleChars()
    {
        $this->assertEquals('HelloWorld', StringHelper::cleanInvisibleChars("Hello\x00World"));
        $this->assertEquals('Hello World', StringHelper::cleanInvisibleChars('Hello World'));
        $this->assertEquals('HelloWorld', StringHelper::cleanInvisibleChars('Hello%00World', true));
    }

    public function testClearNewLines()
    {
        $this->assertEquals('Hello World', StringHelper::clearCRLF("Hello\r\nWorld"));
        $this->assertEquals('Hello World', StringHelper::clearCRLF("Hello\rWorld"));
        $this->assertEquals('Hello World', StringHelper::clearCRLF("Hello\nWorld"));
        $this->assertEquals('Hello World', StringHelper::clearCRLF('Hello World'));
    }

    public function testClassName()
    {
        $this->assertEquals('StringHelper', StringHelper::className(new StringHelper(), true));
        $this->assertEquals('Scaleum\Stdlib\Helpers\StringHelper', StringHelper::className(new StringHelper()));
        $this->assertEquals('StringHelper', StringHelper::className('Scaleum\Stdlib\Helpers\StringHelper', true));
        $this->assertEquals('Scaleum\Stdlib\Helpers\StringHelper', StringHelper::className('Scaleum\Stdlib\Helpers\StringHelper'));
    }

    public function testNormalizeName()
    {
        $this->assertEquals('helloworld', StringHelper::normalizeName('Hello World'));
        $this->assertEquals('helloworld', StringHelper::normalizeName('Hello-World'));
        $this->assertEquals('helloworld', StringHelper::normalizeName('Hello_World'));
        $this->assertEquals('helloworld', StringHelper::normalizeName('Hello\\World'));
        $this->assertEquals('helloworld', StringHelper::normalizeName('Hello/World'));
    }

    public function testLimitLength()
    {
        $this->assertEquals('Hello...', StringHelper::limitLength('Hello World', 8));
        $this->assertEquals('Hello World', StringHelper::limitLength('Hello World', 20));
        $this->assertEquals('Hello Wo', StringHelper::limitLength('Hello World', 8, ''));
    }
}