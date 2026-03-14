<?php
use PHPUnit\Framework\TestCase;
use Scaleum\Stdlib\Helpers\ArrayHelper;

class ArrayHelperTest extends TestCase
{
    public function testElementWithExistingKey()
    {
        $array = ['a' => 1, 'b' => 2];
        $this->assertEquals(1, ArrayHelper::element('a', $array));
    }

    public function testElementWithNonExistingKey()
    {
        $array = ['a' => 1, 'b' => 2];
        $this->assertEquals(false, ArrayHelper::element('c', $array));
    }

    public function testElementWithTypeChecking()
    {
        $array = ['a' => 1, 'b' => '2'];
        $this->assertEquals(1, ArrayHelper::element('a', $array, false, 'int'));
        $this->assertEquals(false, ArrayHelper::element('b', $array, false, 'int'));
    }

    public function testElementsWithMultipleKeys()
    {
        $array = ['a' => 1, 'b' => 2, 'c' => 3];
        $expected = ['a' => 1, 'b' => 2];
        $this->assertEquals($expected, ArrayHelper::elements(['a', 'b'], $array, false, null, true));
    }

    public function testElementsWithNonExistingKeys()
    {
        $array = ['a' => 1, 'b' => 2];
        $expected = ['a' => 1, 'c' => false];
        $this->assertEquals($expected, ArrayHelper::elements(['a', 'c'], $array, false, null, true));
    }

    public function testElementsWithTypeChecking()
    {
        $array = ['a' => 1, 'b' => '2'];
        $expected = ['a' => 1, 'b' => false];
        $this->assertEquals($expected, ArrayHelper::elements(['a', 'b'], $array, false, ['int', 'int'], true));
    }

    public function testFilterWithKeysToRemove()
    {
        $array = ['a' => 1, 'b' => 2, 'c' => 3];
        $expected = ['a' => 1, 'c' => 3];
        $this->assertEquals($expected, ArrayHelper::filter(['b'], $array));
    }

    public function testFilterWithNoKeysToRemove()
    {
        $array = ['a' => 1, 'b' => 2];
        $this->assertEquals($array, ArrayHelper::filter([], $array));
    }

    public function testKeyFirstWithNonEmptyArray()
    {
        $array = ['a' => 1, 'b' => 2];
        $this->assertEquals('a', ArrayHelper::keyFirst($array));
    }

    public function testKeyFirstWithEmptyArray()
    {
        $array = [];
        $this->assertNull(ArrayHelper::keyFirst($array));
    }

    public function testKeyLastWithNonEmptyArray()
    {
        $array = ['a' => 1, 'b' => 2];
        $this->assertEquals('b', ArrayHelper::keyLast($array));
    }

    public function testKeyLastWithEmptyArray()
    {
        $array = [];
        $this->assertNull(ArrayHelper::keyLast($array));
    }

    public function testKeysExistsWithAllKeysExisting()
    {
        $array = ['a' => 1, 'b' => 2];
        $this->assertTrue(ArrayHelper::keysExists(['a', 'b'], $array));
    }

    public function testKeysExistsWithSomeKeysMissing()
    {
        $array = ['a' => 1, 'b' => 2];
        $this->assertFalse(ArrayHelper::keysExists(['x', 'y'], $array));
    }
}