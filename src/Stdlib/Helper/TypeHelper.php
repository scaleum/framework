<?php
/**
 * This file is part of Scaleum\Stdlib.
 *
 * (C) 2009-2025 Maxim Kirichenko <kirichenko.maxim@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Scaleum\Stdlib\Helper;

class TypeHelper
{
    const TYPE_ARRAY    = 'array';
    const TYPE_BOOL     = 'bool';
    const TYPE_CALLABLE = 'callable';
    const TYPE_FLOAT    = 'float';
    const TYPE_INT      = 'int';
    const TYPE_NULL     = 'null';
    const TYPE_NUMERIC  = 'numeric';
    const TYPE_OBJECT   = 'object';
    const TYPE_RESOURCE = 'resource';
    const TYPE_STRING   = 'string';

    const TYPES = [
      self::TYPE_ARRAY,
      self::TYPE_BOOL,
      self::TYPE_CALLABLE,
      self::TYPE_FLOAT,
      self::TYPE_INT,
      self::TYPE_NULL,
      self::TYPE_NUMERIC,
      self::TYPE_OBJECT,
      self::TYPE_RESOURCE,
      self::TYPE_STRING,
    ];

    /**
     * Get the type of a variable
     *
     * @param $var
     *
     * @return string
     */
    public static function getType($var)
    {
        foreach (self::TYPES as $type) {
            if (function_exists( $func = "is_{$type}" )) {
                if (call_user_func( $func, $var ) === true) {
                    return $type;
                }
            }
        }

        return 'unknown';
    }

    /**
     * Checks if a variable of the specified type is
     *
     * @param mixed  $var
     * @param string $type
     *
     * @return bool
     */
    public static function isType($var, $type = self::TYPE_NULL)
    {
        if (function_exists( $func = "is_{$type}" )) {
            return call_user_func( $func, $var );
        }

        return false;
    }
}

/* End of file TypeHelper.php */
