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
 * Collection
 *
 * @author Maxim Kirichenko <kirichenko.maxim@gmail.com>
 * @datetime 09.01.2025 17:12:00
 */
class Collection extends \stdClass implements \Iterator, \ArrayAccess, \Countable {
    /**
     * The inner array
     * @var array
     */
    protected $elements = [];

    public function __construct(?array $array = null) {
        if ($array !== null) {
            $this->merge($array, true);
        }
    }
    /**
     * Get all elements as an array
     */
    public function __toArray(): array {
        return $this->elements;
    }

    /**
     * Convert elements to a serialized string
     */
    public function __toString(): string {
        return serialize($this->elements);
    }

    /**
     * Convert collection to XML format
     */
    public function __toXml(): string {
        $xml = new \SimpleXMLElement('<Collection/>');
        foreach ($this->elements as $key => $value) {
            $item = $xml->addChild('element', htmlspecialchars((string) $value));
            $item->addAttribute('key', (string) $key);
        }
        return $xml->asXML();
    }

    /**
     * Set an item to the collection
     *
     * @param string|int $key Key to associate with the item
     * @param mixed $item The item to append
     */
    public function set($key, $item): void {
        if (!array_key_exists($key, $this->elements)) {
            $this->elements[$key] = $item;
        } else {
            if (!is_array($this->elements[$key])) {
                $this->elements[$key] = [$this->elements[$key]];
            }
            $this->elements[$key][] = $item;
        }
    }


    public function asort(?int $flags = SORT_REGULAR) {
        asort($this->elements, $flags);

        return $this;
    }

    public function back():mixed {
        return prev($this->elements);
    }

    public function clear() {
        $this->elements = [];
        return $this;
    }

    /**
     * Count elements of the collection
     */
    public function count(): int {
        return count($this->elements);
    }

    public function current(): mixed {
        return current($this->elements);
    }

    public function currentKey(): mixed {
        return key($this->elements);
    }

    public function exists($key) {
        return array_key_exists($key, $this->elements);
    }

    public function fetch(): mixed {
        if ($this->valid()) {
            $result = $this->current();
            $this->next();
            return $result;
        } else {
            return false;
        }
    }

    public function forward(): mixed {
        return end($this->elements);
    }

    public function get($key, $default = null): mixed {
        $result = $default;
        if ($this->exists($key)) {
            $result = $this->elements[$key];
        }

        return $result;
    }

    public function hasNext(): bool {
        $this->next();
        $result = $this->valid();
        $this->back();

        return $result;
    }

    public function indexOf($search): mixed {
        $result = null;
        foreach ($this->elements as $key => $value) {
            if ($value === $search) {
                $result = $key;
                break;
            }
        }
        $this->rewind();

        return $result;
    }

    public function isEmpty(): bool {
        return ($this->count() == 0) == true;
    }

    public function isValid(): bool {
        return $this->valid() == true;
    }

    public function key(): mixed {
        return $this->currentKey();
    }

    public function ksort(?int $flags = SORT_REGULAR ): self {
        ksort($this->elements, $flags);
        return $this;
    }

    public function lastIndexOf($obj): mixed {
        $return = null;
        foreach ($this->elements as $key => $element) {
            if ($element === $obj) {
                $return = $key;
            }
        }
        $this->rewind();

        return $return;
    }

    /**
     * Merge an array into the collection
     *
     * @param array $array Elements to merge
     * @param bool $overwrite Whether to overwrite existing keys
     */
    public function merge(array $array, bool $overwrite = false): void {
        foreach ($array as $key => $value) {
            if (is_array($value) && isset($this->elements[$key]) && is_array($this->elements[$key])) {
                $this->elements[$key] = $overwrite
                ? array_replace_recursive($this->elements[$key], $value)
                : array_merge_recursive($this->elements[$key], $value);
            } else {
                if ($overwrite || !array_key_exists($key, $this->elements)) {
                    $this->elements[$key] = $value;
                }
            }
        }
    }

    public function next(): void {
        next($this->elements);
    }

    public function offsetExists($offset): bool {
        return $this->exists($offset);
    }

    public function offsetGet($offset): mixed {
        return $this->get($offset);
    }

    public function offsetSet($offset, $value): void {
        $this->set($offset, $value);
    }

    public function offsetUnset($offset): void {
        $this->remove($offset);
    }

    public function remove($key): self {
        if (array_key_exists($key, $this->elements)) {
            unset($this->elements[$key]);
            $this->count();
        }

        return $this;
    }

    public function rewind(): void {
        reset($this->elements);
    }

    public function seek($key): bool {
        $this->rewind();
        while ($this->valid()) {
            if ($this->key() == $key) {
                return true;
            }
            $this->next();
        }

        return false;
    }



    public function sort(?int $flags = null): self {
        sort($this->elements, $flags);

        return $this;
    }

    public function trim(int $size): self {
        $tmp            = array_chunk($this->elements, $size, true);
        $this->elements = $tmp[0];
        return $this;
    }

    public function uasort($callback): self {
        if (is_callable($callback)) {
            uasort($this->elements, $callback);
        }

        return $this;
    }

    public function valid(): bool {
        return $this->currentKey() !== null;
    }
}
/** End of Collection **/