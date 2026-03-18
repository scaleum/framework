<?php

use PHPUnit\Framework\TestCase;
use Scaleum\Stdlib\Base\Collection;

class CollectionTest extends TestCase {
    public function testConstruct() {
        $collection = new Collection(['a' => 1, 'b' => 2]);
        $this->assertEquals(['a' => 1, 'b' => 2], $collection->__toArray());
    }

    public function testToArray() {
        $collection = new Collection(['a' => 1, 'b' => 2]);
        $this->assertEquals(['a' => 1, 'b' => 2], $collection->__toArray());
    }

    public function testToString() {
        $collection = new Collection(['a' => 1, 'b' => 2]);
        $this->assertEquals(serialize(['a' => 1, 'b' => 2]), $collection->__toString());
    }

    public function testToXml() {
        $collection = new Collection(['a' => 1, 'b' => 2]);
        $xml = new \SimpleXMLElement('<Collection/>');
        $item1 = $xml->addChild('element', '1');
        $item1->addAttribute('key', 'a');
        $item2 = $xml->addChild('element', '2');
        $item2->addAttribute('key', 'b');
        $this->assertXmlStringEqualsXmlString($xml->asXML(), $collection->__toXml());
    }

    public function testAppend() {
        $collection = new Collection();
        $collection->set('a', 1);
        $collection->set('a', 2);
        $this->assertEquals(['a' => [1, 2]], $collection->__toArray());
    }

    public function testAsort() {
        $collection = new Collection(['b' => 2, 'a' => 1]);
        $collection->asort();
        $this->assertEquals(['a' => 1, 'b' => 2], $collection->__toArray());
    }

    public function testBack() {
        $collection = new Collection(['a' => 1, 'b' => 2]);
        $collection->forward();
        $this->assertEquals(1, $collection->back());
    }

    public function testClear() {
        $collection = new Collection(['a' => 1, 'b' => 2]);
        $collection->clear();
        $this->assertEquals([], $collection->__toArray());
    }

    public function testCount() {
        $collection = new Collection(['a' => 1, 'b' => 2]);
        $this->assertEquals(2, $collection->count());
    }

    public function testCurrent() {
        $collection = new Collection(['a' => 1, 'b' => 2]);
        $this->assertEquals(1, $collection->current());
    }

    public function testCurrentKey() {
        $collection = new Collection(['a' => 1, 'b' => 2]);
        $this->assertEquals('a', $collection->currentKey());
    }

    public function testExists() {
        $collection = new Collection(['a' => 1, 'b' => 2]);
        $this->assertTrue($collection->exists('a'));
        $this->assertFalse($collection->exists('c'));
    }

    public function testFetch() {
        $collection = new Collection(['a' => 1, 'b' => 2]);
        $this->assertEquals(1, $collection->fetch());
        $this->assertEquals(2, $collection->fetch());
        $this->assertFalse($collection->fetch());
    }

    public function testForward() {
        $collection = new Collection(['a' => 1, 'b' => 2]);
        $this->assertEquals(2, $collection->forward());
    }

    public function testGet() {
        $collection = new Collection(['a' => 1, 'b' => 2]);
        $this->assertEquals(1, $collection->get('a'));
        $this->assertEquals(null, $collection->get('c'));
        $this->assertEquals(3, $collection->get('c', 3));
    }

    public function testHasNext() {
        $collection = new Collection(['a' => 1, 'b' => 2]);
        $this->assertTrue($collection->hasNext());
        $collection->fetch();
        $this->assertFalse($collection->hasNext());
    }

    public function testIndexOf() {
        $collection = new Collection(['a' => 1, 'b' => 2]);
        $this->assertEquals('a', $collection->indexOf(1));
        $this->assertNull($collection->indexOf(3));
    }

    public function testIsEmpty() {
        $collection = new Collection();
        $this->assertTrue($collection->isEmpty());
        $collection->set('a', 1);
        $this->assertFalse($collection->isEmpty());
    }

    public function testIsValid() {
        $collection = new Collection(['a' => 1]);
        $this->assertTrue($collection->isValid());
        $collection->fetch();
        $this->assertFalse($collection->isValid());
    }

    public function testKey() {
        $collection = new Collection(['a' => 1, 'b' => 2]);
        $this->assertEquals('a', $collection->key());
    }

    public function testKsort() {
        $collection = new Collection(['b' => 2, 'a' => 1]);
        $collection->ksort();
        $this->assertEquals(['a' => 1, 'b' => 2], $collection->__toArray());
    }

    public function testLastIndexOf() {
        $collection = new Collection(['a' => 1, 'b' => 2, 'c' => 1]);
        $this->assertEquals('c', $collection->lastIndexOf(1));
    }

    public function testMerge() {
        $collection = new Collection(['a' => 1, 'b' => 2]);
        $collection->merge(['b' => 3, 'c' => 4],true);
        $this->assertEquals(['a' => 1, 'b' => 3, 'c' => 4], $collection->__toArray());
    }

    public function testNext() {
        $collection = new Collection(['a' => 1, 'b' => 2]);
        $collection->next();
        $this->assertEquals(2, $collection->current());
    }

    public function testOffsetExists() {
        $collection = new Collection(['a' => 1, 'b' => 2]);
        $this->assertTrue($collection->offsetExists('a'));
        $this->assertFalse($collection->offsetExists('c'));
    }

    public function testOffsetGet() {
        $collection = new Collection(['a' => 1, 'b' => 2]);
        $this->assertEquals(1, $collection->offsetGet('a'));
    }

    public function testOffsetSet() {
        $collection = new Collection(['a' => 1, 'b' => 2]);
        $collection->offsetSet('c', 3);
        $this->assertEquals(3, $collection->offsetGet('c'));
    }
}