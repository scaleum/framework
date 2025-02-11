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
 * Class for working with nested arrays using a path
 *
 * @author Maxim Kirichenko <kirichenko.maxim@gmail.com>
 */
class Registry {
    private array $items      = [];
    private string $separator = '/';
    public function __construct(array $items = [], string $separator = '/') {
        $this->separator = $separator;
        $this->merge($items);
    }

    public function get(string $str, mixed $default = null): mixed {
        $keys  = explode($this->separator, $str);
        $value = $this->items;

        foreach ($keys as $key) {
            if (is_array($value) && array_key_exists($key, $value)) {
                $value = $value[$key];
            } else {
                return $default;
            }
        }

        return $value;
    }

    public function set(string $str, mixed $value): self {
        $keys  = explode($this->separator, $str);
        $items = &$this->items;

        foreach ($keys as $key) {
            if (! isset($items[$key]) || ! is_array($items[$key])) {
                $items[$key] = [];
            }
            $items = &$items[$key];
        }

        $items = (is_array($value)) ? ArrayHelper::merge($items, $value) : $value;
        return $this;
    }

    public function has(string $str): bool {
        $keys  = explode($this->separator, $str);
        $value = $this->items;

        foreach ($keys as $key) {
            if (is_array($value) && array_key_exists($key, $value)) {
                $value = $value[$key];
            } else {
                return false;
            }
        }

        return true;
    }

    public function merge(array $items, ?string $key = null): self {
        if ($key) {
            $this->set($key, $items);
        } else {
            $this->items = ArrayHelper::merge($this->items, $items);
        }
        return $this;
    }

    public function unset(string $str): void {
        $keys  = explode($this->separator, $str);
        $items = &$this->items;

        foreach ($keys as $key) {
            if (isset($items[$key])) {
                if (end($keys) === $key) {
                    unset($items[$key]);
                } else {
                    $items = &$items[$key];
                }
            } else {
                break;
            }
        }
        unset($items);
    }

    /**
     * Get the value of separator
     */
    public function getSeparator() {
        return $this->separator;
    }

    /**
     * Set the value of separator
     *
     * @return  self
     */
    public function setSeparator($separator) {
        $this->separator = $separator;

        return $this;
    }
}
/** End of Registry **/