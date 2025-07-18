<?php
declare(strict_types=1);
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

/**
 * Json
 *
 * @author Maxim Kirichenko <kirichenko.maxim@gmail.com>
 */
class Json extends LoaderAbstract implements LoaderInterface
{
    public function fromFile(string $filename): array {
        $this->validate($filename);
        return $this->fromString(file_get_contents($filename));
    }

    public function fromString(string $str): array {
        $result = [];
        if (! is_array($result = json_decode($str,true))) {
            throw new ERuntimeError(sprintf("String JSON '%s' cannot be converted to array.", $str));
        }

        return $result;
    }
}
/** End of Json **/