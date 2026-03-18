<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Scaleum\Events\Event;
use Scaleum\Events\EventManager;
use Scaleum\Stdlib\Exceptions\ETypeException;

final class EventManagerTest extends TestCase
{
    public function testOnRejectsNonCallableCallback(): void
    {
        $events = new EventManager();

        $this->expectException(ETypeException::class);
        $events->on('demo.event', 'not_existing_function_name_12345');
    }

    public function testOneOffListenerRemovedEvenWhenShortCircuitCallbackBreaksLoop(): void
    {
        $events = new EventManager();
        $invocations = 0;

        $listener = $events->on('demo.event', static function () use (&$invocations): string {
            $invocations++;
            return 'hit';
        });

        $listener->setOneOff(true);

        $first = $events->dispatch('demo.event', null, [], static fn(string $result): bool => $result === 'hit');
        $second = $events->dispatch('demo.event');

        self::assertSame(['hit'], $first);
        self::assertSame([], $second);
        self::assertSame(1, $invocations);
    }

    public function testListenersAreExecutedInAscendingPriorityOrder(): void
    {
        $events = new EventManager();
        $calls = [];

        $events->on('demo.event', static function () use (&$calls): void {
            $calls[] = 'p10';
        }, 10);

        $events->on('demo.event', static function () use (&$calls): void {
            $calls[] = 'p-1';
        }, -1);

        $events->on('demo.event', static function () use (&$calls): void {
            $calls[] = 'p2';
        }, 2);

        $events->dispatch('demo.event');

        self::assertSame(['p-1', 'p2', 'p10'], $calls);
    }

    public function testWildcardListenerRunsForNamedEvent(): void
    {
        $events = new EventManager();
        $calls = [];

        $events->on('*', static function (Event $event) use (&$calls): void {
            $calls[] = 'wildcard:' . $event->getName();
        }, 0);

        $events->on('demo.event', static function () use (&$calls): void {
            $calls[] = 'named';
        }, 1);

        $events->dispatch('demo.event');

        self::assertSame(['wildcard:demo.event', 'named'], $calls);
    }

    public function testFireStopBreaksDispatchChain(): void
    {
        $events = new EventManager();
        $calls = [];

        $events->on('demo.event', static function (Event $event) use (&$calls): string {
            $calls[] = 'first';
            $event->fireStop();
            return 'first-result';
        }, 0);

        $events->on('demo.event', static function () use (&$calls): string {
            $calls[] = 'second';
            return 'second-result';
        }, 1);

        $result = $events->dispatch('demo.event');

        self::assertSame(['first'], $calls);
        self::assertSame(['first-result'], $result);
    }

    public function testFalseyResultsAreNotAddedToEffects(): void
    {
        $events = new EventManager();

        $events->on('demo.event', static fn(): string => '0', 0);
        $events->on('demo.event', static fn(): int => 0, 1);
        $events->on('demo.event', static fn(): string => '', 2);
        $events->on('demo.event', static fn(): array => [], 3);
        $events->on('demo.event', static fn(): bool => false, 4);
        $events->on('demo.event', static fn(): string => 'ok', 5);

        $result = $events->dispatch('demo.event');

        self::assertSame(['ok'], $result);
    }

    public function testOneOffWildcardListenerIsRemovedAfterFirstDispatch(): void
    {
        $events = new EventManager();
        $hits = 0;

        $listener = $events->on('*', static function () use (&$hits): string {
            $hits++;
            return 'wild';
        }, 0);

        $listener->setOneOff(true);

        $events->dispatch('event.a');
        $events->dispatch('event.b');

        self::assertSame(1, $hits);
    }
}
