<?php

declare (strict_types = 1);
/**
 * This file is part of Scaleum\Storages\Redis.
 *
 * (C) 2009-2025 Maxim Kirichenko <kirichenko.maxim@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Scaleum\Storages\Redis;

use Scaleum\Stdlib\Base\Hydrator;

class Message extends Hydrator {
    protected $attributes = [];

    public function __get($name) {
        return $this->getAttribute($name);
    }

    public function __set($name, $value) {
        $this->setAttribute($name, $value);
    }

    public function deleteAttribute(string $key): static
    {
        if ($this->hasAttribute($key)) {
            unset($this->attributes[$key]);
        }
        return $this;
    }

    public function getAttribute(string $key, mixed $default = null): mixed {
        if ($this->hasAttribute($key)) {
            return $this->attributes[$key];
        }

        return $default;
    }

    public function getAttributes(): array {
        return $this->attributes;
    }

    public function hasAttribute(string $key): bool {
        return array_key_exists($key, $this->attributes);
    }

    public function setAttribute(string $key, mixed $value = null): static
    {
        $this->attributes[$key] = $value;
        return $this;
    }

    public function setAttributes(array $attributes): static
    {
        $this->attributes = $attributes;
        return $this;
    }
}

/* End of file Predicable.php */
