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

namespace Scaleum\Config\Loaders;

use Scaleum\Stdlib\Exceptions\ERuntimeError;
use Scaleum\Stdlib\Helpers\StringHelper;

/**
 * Php
 *
 * @author Maxim Kirichenko <kirichenko.maxim@gmail.com>
 */
class PhpArray extends LoaderAbstract implements LoaderInterface {
    public function fromFile(string $filename): array {
        $this->validate($filename);

        $load = \Closure::bind(function ($filename) {
            return require $filename;
        }, $this);

        $result = $load($filename);
        if (! is_array($result)) {
            throw new ERuntimeError(sprintf('File "%s" must return an array.', $filename));
        }

        return $result;
    }

    public function fromString(string $str): array {
        $result = [];
        if(StringHelper::isSerialized($str)) {
            $thing = @unserialize($str);
            if($thing !== false) {
                $result = json_decode(json_encode($thing), true);
            }
        }

        if (! is_array($result)) {
            throw new ERuntimeError(sprintf("String '%s' cannot be converted to array.", $str));
        }

        return $result;
    }
}
/** End of Php **/