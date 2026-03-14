<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Scaleum\Events\Event;
use Scaleum\Stdlib\Base\Hydrator;

class EventTest extends TestCase
{
    public function testEventCanBeInstantiated(): void
    {
        $event = new Event();
        $this->assertInstanceOf(Event::class, $event);
    }

    public function testEventExtendsAutoInitialized(): void
    {
        $event = new Event();
        $this->assertInstanceOf(Hydrator::class, $event);
    }

    public function testGetNameDefaultsToEmptyString(): void
    {
        $event = new Event();

        $this->assertSame('', $event->getName());
    }

    public function testParamsAccessUsesArrayContract(): void
    {
        $event = new Event();

        $event->setParam('foo', 'bar');

        $this->assertSame('bar', $event->getParam('foo'));
        $this->assertSame('fallback', $event->getParam('missing', 'fallback'));
        $this->assertSame(['foo' => 'bar'], $event->getParams());
    }
}