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

use Scaleum\i18n\Contracts\TranslationLoaderInterface;

/**
 * Summary of TranslationLoaderAbstract
 */
abstract class TranslationLoaderAbstract implements TranslationLoaderInterface {
    public function validateFile(string $filename):bool {
        return is_file($filename) && is_readable($filename);
    }
}

/* End of file TranslationLoaderAbstract.php */
