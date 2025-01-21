<?php
declare (strict_types = 1);
/**
 * This file is part of Scaleum Framework.
 *
 * (C) 2009-2025 Maxim Kirichenko <kirichenko.maxim@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Scaleum\Events;

use Scaleum\Stdlib\Exception\EObjectError;
use Scaleum\Stdlib\Exception\ETypeError;

/**
 * EventManager
 *
 * @author Maxim Kirichenko <kirichenko.maxim@gmail.com>
 */
class EventManager implements EventManagerInterface {
    protected array $events = [];

    public function on(string | Event | array $event, mixed $callback = null, int $priority = 1): array | Listener {
        if ($callback === null) {
            throw new ETypeError(sprintf('%s: expects a callback; null provided', __METHOD__));
        }

        if (is_array($event)) {
            $listeners = [];
            foreach ($event as $name) {
                $listeners[] = $this->on($name, $callback, $priority);
            }

            return $listeners;
        }

        if ($event instanceof Event) {
            $event = $event->getName();
        }

        if (empty($this->events[$event])) {
            $this->events[$event] = [];
        }

        $listener               = new Listener($callback, $event, $priority);
        $this->events[$event][] = $listener;

        return $listener;
    }

    public function getEvents(): array {
        return array_keys($this->events);
    }

    public function getListeners($event): array {
        $result = [];
        foreach ([$event, '*'] as $key) {
            if (array_key_exists($key, $this->events)) {
                $result = array_merge($result, $this->events[$key]);
            }
        }

        uasort($result, function ($a, $b) {
            return $a->getPriority() - $b->getPriority();
        });
        return $result;
    }

    public function remove(Listener $listener): bool {
        $event = $listener->getEvent();
        if (! $event || empty($this->events[$event])) {
            return false;
        }

        foreach ($this->events[$event] as $key => $value) {
            if ($value === $listener) {
                unset($this->events[$event][$key]);
                if (count($this->events[$event]) == 0) {
                    unset($this->events[$event]);
                }
                return true;
            }
        }

        return false;
    }

    public function dispatch(string | Event $event, mixed $context = null, array $params = [], mixed $callback = null): array {
        if ($callback && ! is_callable($callback)) {
            throw new EObjectError('Invalid callback provided');
        }

        if ($event instanceof Event) {
            return $this->dispatchInternal($event->getName(), $event, $callback);
        } else {
            return $this->dispatchInternal($event, new Event(['name' => $event, 'context' => $context, 'params' => $params]), $callback);
        }
    }

    protected function dispatchInternal(string $event, EventInterface $ref, mixed $callback = null) {
        $effect    = [];
        $listeners = $this->getListeners($event);
        foreach ($listeners as $listener) {
            /** @var Listener $listener */
            $result = call_user_func($listener->getCallback(), $ref);
            if (! empty($result)) {
                $effect[] = $result;
                if ($callback && call_user_func($callback, $result)) {
                    break;
                }
            }

            if ($listener->isOneOff()) {
                $this->remove($listener);
            }

            if ($ref->fireStopped()) {
                break;
            }
        }

        return $effect;
    }
}
/** End of EventManager **/