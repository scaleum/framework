<?php

declare(strict_types=1);
/**
 * This file is part of Scaleum\Stdlib.
 *
 * (C) 2009-2025 Maxim Kirichenko <kirichenko.maxim@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Scaleum\Stdlib\Base;

use Scaleum\Stdlib\Helpers\StringHelper;
use RuntimeException;

/**
 * AutoInitialized
 *
 * @author Maxim Kirichenko <kirichenko.maxim@gmail.com>
 * @datetime 07.05.2024 11:20:55
 */
class AutoInitialized
{
    use InitTrait;
    public function __construct(array $config = [])
    {
        $this->initialize($config);
    }

    public static function turnInto(mixed $input): mixed
    {
        $result = null;

        $class_name = null;
        $args = [];
        if (is_string($input)) {
            $class_name = $input;
        } elseif (is_array($input) && count($input) > 0) {
            $class_name = isset($input['class']) ? $input['class'] : null;
            $args = isset($input['config']) ? $input['config'] : array_diff_key($input, array_fill_keys(['class', 'config'], 'empty'));
        }

        if (!class_exists((string)$class_name)) {
            throw new RuntimeException(sprintf('%s: failed retrieving class name "%s" via mixed "%s"; class does not exist', __METHOD__, StringHelper::className($class_name, true), gettype($class_name)));
        }

        if (count($args) > 0) {
            $result = new $class_name($args);
        } else {
            $result = new $class_name();
        }

        return $result;
    }
}
/** End of AutoInitialized **/
