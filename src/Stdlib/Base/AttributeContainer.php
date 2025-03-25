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

namespace Scaleum\Stdlib\Base;

/**
 * AttributeContainer
 *
 * @author Maxim Kirichenko <kirichenko.maxim@gmail.com>
 */
class AttributeContainer {
    protected array $attributes = [];
    public function __construct(array $attributes = []) {
        $this->attributes = $attributes;
    }

    public function __get(string $name) {
        return $this->getAttribute($name);
    }

    public function __set(string $name, mixed $value) {
        $this->setAttribute($name, $value);
    }

    public function deleteAttribute(string $key): self {
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

    public function setAttribute(string $key, mixed $value = null): self {
        $this->attributes[$key] = $value;
        return $this;
    }

    public function setAttributes(array $value): self {
        $this->attributes = $value;
        return $this;
    }
}
/** End of AttributeContainer **/