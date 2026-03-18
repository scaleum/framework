<?php

use PHPUnit\Framework\TestCase;
use RuntimeException;
use Scaleum\Stdlib\Base\Hydrator;

class AutoInitializedTest extends TestCase
{
    public function testConstructorWithEmptyArray()
    {
        $autoInitialized = new Hydrator([]);
        $this->assertInstanceOf(Hydrator::class, $autoInitialized);
    }

    public function testConstructorWithNonEmptyArray()
    {
        $config = [];
        $autoInitialized = new Hydrator($config);
        $this->assertInstanceOf(Hydrator::class, $autoInitialized);
    }

    public function testTurnIntoWithValidClassName()
    {
        $result = Hydrator::createInstance(Hydrator::class);
        $this->assertInstanceOf(Hydrator::class, $result);
    }

    public function testTurnIntoWithValidClassNameAndConfig()
    {
        $input = [
            'class' => Hydrator::class,
            'config' => []
        ];
        $result = Hydrator::createInstance($input);
        $this->assertInstanceOf(Hydrator::class, $result);
    }

    public function testTurnIntoWithValidClassNameAndNoConfig()
    {
        $input = [
            'class' => Hydrator::class
        ];
        $result = Hydrator::createInstance($input);
        $this->assertInstanceOf(Hydrator::class, $result);
    }

    public function testTurnIntoWithInvalidClassName()
    {
        $this->expectException(RuntimeException::class);
        Hydrator::createInstance('InvalidClassName');
    }

    public function testTurnIntoWithInvalidClassNameInArray()
    {
        $input = [
            'class' => 'InvalidClassName'
        ];
        $this->expectException(RuntimeException::class);
        Hydrator::createInstance($input);
    }
}