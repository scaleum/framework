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

use Scaleum\Stdlib\Base\AutoInitialized;

/**
 * Event
 *
 * @author Maxim Kirichenko <kirichenko.maxim@gmail.com>
 * @datetime 09.01.2025 18:52:00
 */
class Event extends AutoInitialized implements EventInterface {
    protected mixed $context    = null;
    protected bool $fireStopped = false;
    protected string $name;
    protected array $params = [];

    public function fireStop($flag = true) {
        $this->fireStopped = (bool) $flag;
    }

    public function fireStopped(): bool {
        return $this->fireStopped;
    }

    public function getContext(): mixed {
        return $this->context;
    }

    public function getName(): string {
        return $this->name;
    }

    public function getParam(string $name, mixed $default = null) {
        if (is_array($this->params)) {
            if (!array_key_exists($name, $this->params)) {
                return $default;
            }

            return $this->params[$name];
        }

        if (!array_key_exists($name, $this->params)) {
            return $default;
        }

        return $this->params->{$name};
    }

    public function getParams(): array {
        return $this->params;
    }

    public function setContext($context): self {
        $this->context = $context;
        return $this;
    }

    public function setName(string $name): self {
        $this->name = $name;
        return $this;
    }

    public function setParam(string $name, mixed $value): self {
        if (is_array($this->params)) {
            $this->params[$name] = $value;
        } else {
            $this->params->{$name} = $value;
        }

        return $this;
    }

    public function setParams(array $params):self {
        $this->params = $params;
        return $this;
    }
}
/** End of Event **/