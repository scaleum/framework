<?php
/**
 * @author    Maxim Kirichenko
 * @copyright Copyright (c) 2009-2017 Maxim Kirichenko (kirichenko.maxim@gmail.com)
 * @license   GNU General Public License v3.0 or later
 */

namespace Scaleum\i18n\Loaders;

use ArrayObject;
use Scaleum\Stdlib\Exceptions\EInOutException;

class Gettext extends TranslationLoaderAbstract {
    public function load(string $filename): ArrayObject {
        if (! $this->validateFile($filename)) {
            throw new EInOutException(
                sprintf(
                    'Could not find or open file %s for reading',
                    $filename
                )
            );
        }

        $messages   = [];
        $matches    = [];
        $matchCount = preg_match_all('/msgid\s+((?:".*(?<!\\\\)"\s*)+)\s+msgstr\s+((?:".*(?<!\\\\)"\s*)+)/', file_get_contents($filename), $matches);
        for ($i = 0; $i < $matchCount; ++$i) {
            $message            = $this->decode($matches[1][$i]);
            $translation        = $this->decode($matches[2][$i]);
            $messages[$message] = $translation;
        }

        return new ArrayObject($messages);
    }

    protected function decode(string $str): string {
        $str = preg_replace(
            ['/"\s+"/', '/\\\\n/', '/\\\\r/', '/\\\\t/', '/\\\\"/'],
            ['', "\n", "\r", "\t", '"'],
            $str
        );

        return substr(rtrim($str), 1, -1);
    }
}

/* End of file Gettext.php */
