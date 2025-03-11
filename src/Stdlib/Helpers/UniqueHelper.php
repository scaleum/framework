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

namespace Scaleum\Stdlib\Helpers;

/**
 * UniqueHelper
 *
 * @author Maxim Kirichenko <kirichenko.maxim@gmail.com>
 */
class UniqueHelper {

    public static function getUniqueID(?string $prefix = NULL): string {
        $result = '';
        if ($prefix !== NULL) {
            $result .= $prefix;
        }

        return md5(uniqid($result, TRUE));
    }

    public static function getUniquePrefix(int $prefix_size = 32) {
        $result = '';
        while (strlen($result) < $prefix_size) {
            $result .= mt_rand(0, mt_getrandmax());
        }

        return $result;
    }
}
/** End of UniqueHelper **/