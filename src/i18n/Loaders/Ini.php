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

/**
 * Class Ini
 * @subpackage Avant\i18n\IO
 */
class Ini extends TranslationLoaderAbstract {
    public function load(string $filename): ArrayObject {
        if (! $this->validateFile($filename)) {
            throw new EInOutException(
                sprintf(
                    'Could not find or open file %s for reading',
                    $filename
                )
            );
        }

        $messages = parse_ini_file($filename, true);

        if (! is_array($messages)) {
            throw new ETypeException(
                sprintf(
                    'Expected an array, given %s',
                    gettype($messages)
                )
            );
        }

        return new ArrayObject($messages);
    }
}

/* End of file Ini.php */
