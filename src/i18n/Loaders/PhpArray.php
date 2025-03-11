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

namespace Scaleum\i18n\Loaders;

use ArrayObject;
use Scaleum\Stdlib\Exceptions\EInOutException;
use Scaleum\Stdlib\Exceptions\ETypeException;

class PhpArray extends TranslationLoaderAbstract {
    public function load(string $filename): ArrayObject {
        if (! $this->validateFile($filename)) {
            throw new EInOutException(
                sprintf(
                    'Could not find or open file %s for reading',
                    $filename
                )
            );
        }
        $load = \Closure::bind(function ($filename) {
            return require $filename;
        }, $this);

        $messages = $load($filename);

        if (! is_array($messages)) {
            throw new ETypeException(
                sprintf(
                    'File "%s" must return an array, given %s.',
                    $filename, gettype($messages)
                )
            );
        }

        return new ArrayObject($messages);
    }
}

/* End of file PhpArray.php */
