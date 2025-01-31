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

namespace Scaleum\Config\Loader;

use Scaleum\Stdlib\Exceptions\ERuntimeError;

/**
 * LoaderAbstract
 *
 * @author Maxim Kirichenko <kirichenko.maxim@gmail.com>
 */
abstract class LoaderAbstract {
    public function validate(string $filename): void {
        if (! is_file($filename) || ! is_readable($filename)) {
            throw new ERuntimeError(sprintf(
                "File '%s' doesn't exist or not readable",
                $filename
            ));
        }
    }
}
/** End of LoaderAbstract **/