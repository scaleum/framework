<?php

use PHPUnit\Framework\TestCase;
use Scaleum\Stdlib\Helper\JsonHelper;

class JsonHelperTest extends TestCase
{
    public function testIsJsonWithValidJson()
    {
        $this->assertTrue(JsonHelper::isJson('{"key": "value"}'));
        $this->assertTrue(JsonHelper::isJson('[1, 2, 3]'));
    }

    public function testIsJsonWithInvalidJson()
    {
        $this->assertFalse(JsonHelper::isJson('{"key": "value"'));
        $this->assertFalse(JsonHelper::isJson('not a json string'));
    }

    public function testIsJsonWithNonStringInput()
    {
        $this->assertFalse(JsonHelper::isJson(123));
        $this->assertFalse(JsonHelper::isJson(['key' => 'value']));
    }

    public function testEncodeWithDefaultFlags()
    {
        $data = ['key' => 'value'];
        $json = JsonHelper::encode($data);
        $this->assertJson($json);
        $this->assertEquals('{"key":"value"}', $json);
    }

    public function testEncodeWithCustomFlags()
    {
        $data = ['key' => 'value'];
        $json = JsonHelper::encode($data, JSON_PRETTY_PRINT);
        $this->assertJson($json);
        $this->assertStringContainsString("\n", $json);
    }

    public function testEncodeWithVariousDataTypes()
    {
        $this->assertEquals('"string"', JsonHelper::encode('string'));
        $this->assertEquals('123', JsonHelper::encode(123));
        $this->assertEquals('true', JsonHelper::encode(true));
        $this->assertEquals('null', JsonHelper::encode(null));
    }

    public function testEncodeWithError()
    {
        $this->expectException(\RuntimeException::class);
        $data = ["\xB1\x31"];
        JsonHelper::encode($data,0);
    }
}