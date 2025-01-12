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

use Scaleum\Stdlib\Base\Collection;
use Scaleum\Stdlib\Exception\EObjectError;
use Scaleum\Stdlib\Exception\ETypeError;

/**
 * EventManager
 *
 * @author Maxim Kirichenko <kirichenko.maxim@gmail.com>
 * @datetime 09.01.2025 20:10:00
 */
class EventManager {
    protected array $events = [];

    public function bind(string | array $event, mixed $callback = null, int $priority = 1) {
        if ($callback === null) {
            throw new ETypeError(sprintf('%s: expects a callback; null provided', __METHOD__));
        }

        if (is_array($event)) {
            $listeners = [];
            foreach ($event as $name) {
                $listeners[] = $this->bind($name, $callback, $priority);
            }

            return $listeners;
        }

        if (empty($this->events[$event])) {
            $this->events[$event] = new Collection();
        }

        $listener = new Listener($callback, $event, $priority);
        $this->events[$event]->append($listener);

        return $listener;
    }

    public function bindFromArray(array $array = []): self {
        if (!is_array($array) || empty($array)) {
            return $this;
        }
        if (!isset($array['event']) || !isset($array['callback'])) {
            return $this;
        }

        $event    = $array['event'];
        $callback = $array['callback'];
        $priority = $array['priority'] ?? 1;

        $this->bind($event, $callback, $priority);
        return $this;
    }

    public function getEvents():array {
        return array_keys($this->events);
    }

    public function getListeners($event):Collection {
        $result = new Collection();

        foreach ([$event, '*'] as $key) {
            if (array_key_exists($key, $this->events)) {
                $result->merge($this->events[$key]);
            }
        }

        $result->uasort(function ($a, $b) {
            return $a->getPriority() - $b->getPriority();
        }
        );

        return $result;
    }

    public function remove(Listener $listener):bool {
        $event = $listener->getEvent();
        if (!$event || empty($this->events[$event])) {
            return false;
        }

        if (($key = $this->events[$event]->indexOf($listener)) !== null) {
            $this->events[$event]->remove($key);
            if ($this->events[$event]->count() == 0) {
                unset($this->events[$event]);
            }

            return true;
        }

        return false;
    }

    public function trigger(string|Event $event, mixed $context = null, array $params = [], mixed $callback = null) {
        if ($callback && !is_callable($callback)) {
            throw new EObjectError('Invalid callback provided');
        }

        if ($event instanceof Event) {
            return $this->triggerInternal($event->getName(), $event, $callback);
        } else {
            return $this->triggerInternal($event, new Event(['name' => $event, 'context' => $context, 'params' => $params]), $callback);
        }
    }

    protected function triggerInternal(string $event, EventInterface $reference, mixed $callback = null) {
        $effect    = new Collection();
        $listeners = $this->getListeners($event);
        foreach ($listeners as $listener) {
            /** @var Listener $listener */
            $result = call_user_func($listener->getCallback(), $reference);
            if (!empty($result)) {
                $effect->append($event,$result);
                if ($callback && call_user_func($callback, $result)) {
                    break;
                }
            }

            if ($listener->isOneOff()) {
                $this->remove($listener);
            }

            if ($reference->fireStopped()) {
                break;
            }
        }

        return $effect;
    }
}
/** End of EventManager **/