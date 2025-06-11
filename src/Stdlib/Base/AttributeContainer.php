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

use Scaleum\Stdlib\Helpers\ArrayHelper;

/**
 * AttributeContainer
 *
 * @author Maxim Kirichenko <kirichenko.maxim@gmail.com>
 */
class AttributeContainer implements AttributeContainerInterface {
    protected array $attributes = [];
    public function __construct(array $attributes = []) {
        $this->setAttributes($attributes);
    }

    public function __get(string $name) {
        return $this->getAttribute($name);
    }

    public function __set(string $name, mixed $value) {
        $this->setAttribute($name, $value);
    }

    public function deleteAttribute(string $key): static {
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

    public function getAttributeCount(): int {
        return count($this->attributes);
    }
    
    public function getAttributes(): array {
        return $this->attributes;
    }

    public function hasAttribute(string $key): bool {
        return array_key_exists($key, $this->attributes);
    }

    public function setAttribute(string $key, mixed $value = null, bool $overwrite = true): static {
        if (array_key_exists($key, $this->attributes) && ! $overwrite) {
            return $this;
        }

        if (array_key_exists($key, $this->attributes)) {
            if (is_array($this->attributes[$key])) {
                $this->attributes[$key] = is_array($value)
                ? ArrayHelper::merge($this->attributes[$key], $value)
                : [ ...$this->attributes[$key], $value];
            } else {
                $this->attributes[$key] = $value;
            }
        } else {
            $this->attributes[$key] = $value;
        }

        return $this;
    }

    public function setAttributes(array $attributes,bool $overwrite = true): static {
        foreach($attributes as $key => $value){
            $this->setAttribute($key,$value,$overwrite);
        }
        return $this;
    }
}
/** End of AttributeContainer **/