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


use Scaleum\Stdlib\Base\CallbackInstance;

/**
 * Listener
 *
 * @author Maxim Kirichenko <kirichenko.maxim@gmail.com>
 * @datetime 09.01.2025 20:17:00
 */
class Listener extends CallbackInstance {
    protected string $event = '*';
    protected bool $oneOff = false;
    protected int $priority = 1;

    public function __construct(mixed $callback, string $event = '*', int $priority = 1) {
        parent::__construct($callback);

        $this->setEvent($event);
        $this->setPriority($priority);
    }

    public function getEvent(): string {
        return $this->event;
    }

    public function getOneOff(): bool {
        return $this->oneOff;
    }

    public function getPriority(): int {
        return $this->priority;
    }

    public function isOneOff(): bool {
        return $this->oneOff == true;
    }

    public function setEvent(string $event = '*'): self {
        $this->event = $event;
        return $this;
    }

    public function setOneOff(bool $oneOff): self {
        $this->oneOff = $oneOff;
        return $this;
    }

    public function setPriority(int $priority = 1): self {
        $this->priority = $priority;
        return $this;
    }
}
/** End of Listener **/