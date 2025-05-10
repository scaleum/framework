<?php
declare (strict_types = 1);
/**
 * This file is part of Scaleum\Stdlib.
 *
 * (C) 2009-2025 Maxim Kirichenko <kirichenko.maxim@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Scaleum\Stdlib\Helpers;

/**
 * This class provides helper methods for working with arrays.
 */
class ArrayHelper {
    /**
     * Retrieves the value of a specified key from an array.
     *
     * @param mixed $key The key to retrieve the value for.
     * @param array $haystack The array to search for the key.
     * @param mixed $default The default value to return if the key is not found (optional).
     * @param mixed $expectedType The expected type of the value (optional).
     * @return mixed The value of the specified key, or the default value if the key is not found.
     */
    public static function element($key, array $haystack, $default = false, $expectedType = null): mixed {
        if (! is_array($haystack)) {
            return $default;
        }

        $result = $default;
        if (array_key_exists($key, $haystack)) {
            if (in_array($expectedType, TypeHelper::TYPES)) {
                if (TypeHelper::isType($haystack[$key], $expectedType)) {
                    $result = $haystack[$key];
                }
            } else {
                $result = $haystack[$key];
            }
        }

        return $result;
    }

    /**
     * Extracts specified elements from an array.
     *
     * @param mixed $keys The keys of the elements to extract.
     * @param array $haystack The array to extract elements from.
     * @param mixed $default The default value to return if an element is not found.
     * @param mixed $expectedType The expected type of the extracted elements.
     * @param bool $keysPreserve Whether to preserve the keys of the extracted elements.
     * @return array The extracted elements as an associative array.
     */
    public static function elements(mixed $keys, array $haystack, mixed $default = false, mixed $expectedType = null, bool $keysPreserve = false): array {
        if (! is_array($haystack)) {
            return $haystack;
        }

        $result = [];

        if (! is_array($keys)) {
            $keys = [$keys];
        }
        $keysCount = count($keys);

        if (! is_array($default)) {
            $default = array_fill(0, $keysCount, $default);
        }

        if (! is_array($expectedType)) {
            $expectedType = array_fill(0, $keysCount, $expectedType);
        }

        for ($i = 0; $i < $keysCount; $i++) {
            $keyPlaceholder = isset($default[$i]) ? $default[$i] : null;
            if (array_key_exists($keys[$i], $haystack)) {
                $keyValue = $haystack[$keys[$i]];
                if (isset($expectedType[$i]) && in_array($expectedType[$i], TypeHelper::TYPES)) {
                    if (! TypeHelper::isType($haystack[$keys[$i]], $expectedType[$i])) {
                        $keyValue = $keyPlaceholder;
                    }
                }

                if ($keysPreserve == true) {
                    $result[$keys[$i]] = $keyValue;
                } else {
                    $result[] = $keyValue;
                }
            } else {
                if ($keysPreserve == true) {
                    $result[$keys[$i]] = $keyPlaceholder;
                } else {
                    $result[] = $keyPlaceholder;
                }
            }
        }

        return $result;
    }

    /**
     * Filters an array by removing specified elements.
     *
     * @param mixed $keys The keys of the elements to remove.
     * @param array $haystack The array to filter.
     * @return array The filtered array.
     */
    public static function filter(mixed $keys, array $haystack): array {
        if (! is_array($haystack)) {
            return $haystack;
        }

        $result = [];

        if (! is_array($keys)) {
            $keys = [$keys];
        }

        foreach ($haystack as $key => $value) {
            if (! in_array($key, $keys)) {
                $result[$key] = $value;
            }
        }

        return $result;
    }

    /**
     * Get the first key of the given array without affecting
     * the internal array pointer.
     *
     * @param array $array
     *
     * @return mixed|null
     */
    public static function keyFirst(array $array): mixed {
        if (is_array($array) && count($array)) {
            reset($array);

            return key($array);
        }

        return null;
    }

    /**
     * Get the last key of the given array without affecting
     * the internal array pointer.
     *
     * @param array $array
     *
     * @return mixed|null
     */
    public static function keyLast(array $array): mixed {
        $result = null;
        if (is_array($array)) {
            end($array);
            $result = key($array);
        }

        return $result;
    }

    /**
     * Checks if all the specified keys exist in the given array.
     *
     * @param array $keys The keys to check for existence.
     * @param array $haystack The array to search for the keys.
     * @return bool Returns true if all the keys exist, false otherwise.
     */
    public static function keysExists(array $keys, array $haystack): bool {
        return count(array_intersect_key(array_flip($keys), $haystack)) > 0;
    }

    /**
     * Searches for a value in an array.
     *
     * @param mixed $needle The value to search for.
     * @param array $haystack The array to search in.
     * @param bool $strict (optional) Whether to perform a strict comparison. Default is false.
     * @param mixed $column (optional) The column to search in multi-dimensional arrays. Default is null.
     * @return mixed|false The key of the found element, or false if not found.
     */
    public static function search(mixed $needle, array $haystack, bool $strict = false, mixed $column = null) {
        return array_search($needle, $column !== null ? array_column($haystack, $column) : $haystack, $strict);
    }

    /**
     * Checks if the given array is associative.
     *
     * An array is considered associative if it has at least one string key.
     *
     * @param array $array The array to check.
     * @return bool True if the array is associative, false otherwise.
     */
    public static function isAssociative(array $array): bool {
        return (bool) count(array_filter(array_keys($array), 'is_string')) > 0;
    }

    /**
     * Smart-merges multiple arrays into one.
     *
     * This method takes multiple arrays as arguments and merges them into a single array.
     * If the arrays have overlapping keys, the later arrays will overwrite the values of the earlier ones.
     *
     * @param array ...$arrays Variable number of arrays to merge.
     * @return array The merged array.
     */
    public static function merge(array ...$arrays): array {
        $result = [];

        foreach ($arrays as $array) {
            foreach ($array as $key => $value) {
                if (isset($result[$key])) {
                    if (is_array($result[$key]) && is_array($value)) {
                        $result[$key] = self::merge($result[$key], $value);
                    } else {
                        if (! is_numeric($key)) {
                            $result[$key] = $value;
                        } else {
                            if (! in_array($value, $result, true)) {
                                $result[] = $value;
                            }
                        }
                    }
                } else {
                    $result[$key] = $value;
                }
            }
        }

        return $result;
    }

    /**
     * Приводит значения входного ассоциативного массива к «нативным» типам PHP:
     *  - строка из цифр (в том числе с минусом) → int
     *  - числовая строка с точкой или экспонентой → float
     *  - 'true'/'false' → bool
     *  - всё остальное остаётся без изменений
     *
     * @param  array<string, mixed> $items
     * @return array<string, array|int|float|bool|string>
     */
    public static function naturalize(array $items): array {
        $result = [];

        foreach ($items as $key => $value) {
            if (is_string($value)) {
                $lower = strtolower($value);

                // Булевы литералы
                if ($lower === 'true' || $lower === 'false') {
                    $result[$key] = $lower === 'true';
                    continue;
                }

                // Целые числа (включая отрицательные)
                if (preg_match('/^-?\d+$/', $value)) {
                    $result[$key] = (int) $value;
                    continue;
                }

                // Дробные и экспонентные числа
                if (is_numeric($value)) {
                    $result[$key] = (float) $value;
                    continue;
                }
            } elseif (is_array($value)) {
                $result[$key] = self::naturalize($value);
                continue;
            }
            // Остальные типы оставляем как есть
            $result[$key] = $value;
        }

        return $result;
    }

    public static function castToArray(object | array $data): array {
        $result = [];
        foreach ((array) $data as $key => $value) {
            // Remove the service prefixes from the properties
            $cleanKey = preg_replace('/^\x00.*\x00/', '', (string)$key);
            if (is_object($value) || is_array($value)) {
                $result[$cleanKey] = self::castToArray($value);
            } else {
                $result[$cleanKey] = $value;
            }
        }

        return $result;
    }

    public static function castToXml(object | array $data, string $root = 'root'): string {
        $data   = self::castToArray($data);
        $encode = function (array $array, \SimpleXMLElement $xml) use (&$encode): void {
            foreach ($array as $key => $value) {
                $tag = is_numeric($key) ? 'item' : $key;
                if (is_array($value)) {
                    $child = $xml->addChild($tag);
                    $encode($value, $child);
                } else {
                    $xml->addChild($tag, htmlspecialchars((string) $value));
                }
            }
        };

        $xml = new \SimpleXMLElement("<{$root}/>");
        $xml->addAttribute('xmlns:xsi', 'http://www.w3.org/2001/XMLSchema-instance');
        $xml->addAttribute('xsi:noNamespaceSchemaLocation', 'http://www.w3.org/2001/XMLSchema-instance');
        $xml->addAttribute('xmlns:xsd', 'http://www.w3.org/2001/XMLSchema');
        $xml->addAttribute('version', '1.0');
        $xml->addAttribute('encoding', 'UTF-8');

        $encode($data, $xml);

        return $xml->asXML();
    }

    public static function castToSerialize(object | array $data): string {
        $data = self::castToArray($data);
        return serialize($data);
    }
}

/* End of file ArrayHelper.php */
