<?php

declare (strict_types = 1);
/**
 * This file is part of Scaleum\Stdlib.
 *
 * (C) 2009-2025 Maxim Kirichenko <kirichenko.maxim@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Scaleum\Stdlib\Helpers;

class Utf8Helper {
    /**
     * Removes all non-UTF-8 characters from it.
     *
     * @param string $str         The string to be sanitized.
     * @param string $replacement The string-replacement
     *
     * @return  string Clean UTF-8 encoded string
     */
    public static function clean(string $str, string $replacement = '?') {
        $regEx = '/(
            [\xC0-\xC1]                             # недопустимые стартовые байты
            |[\xF5-\xFF]                            # вне допустимого диапазона UTF-8
            |[\x80-\xBF](?![\xC2-\xF4])             # висячие continuation-байты
            |[\xC2-\xDF](?![\x80-\xBF])             # неполные 2-байтовые
            |[\xE0-\xEF](?![\x80-\xBF]{2})          # неполные 3-байтовые
            |[\xF0-\xF4](?![\x80-\xBF]{3})          # неполные 4-байтовые
        )/sx';

        return preg_replace($regEx, $replacement, $str);
    }

    /**
     * Remove UTF-8 BOM char from string
     *
     * @param $str
     *
     * @return mixed
     */
    public static function cleanUtf8Bom(string $str) {
        return str_replace(self::getUtf8Bom(), '', $str);
    }

    public static function getUtf8Bom() {
        return "\xef\xbb\xbf";
    }

    public static function getUtf8WhiteSpaces() {
        $white = [
            0     => "\x0",          //NUL Byte
            9     => "\x9",          //Tab
            10    => "\xa",          //New Line
            11    => "\xb",          //Vertical Tab
            13    => "\xd",          //Carriage Return
            32    => "\x20",         //Ordinary Space
            160   => "\xc2\xa0",     //NO-BREAK SPACE
            5760  => "\xe1\x9a\x80", //OGHAM SPACE MARK
            6158  => "\xe1\xa0\x8e", //MONGOLIAN VOWEL SEPARATOR
            8192  => "\xe2\x80\x80", //EN QUAD
            8193  => "\xe2\x80\x81", //EM QUAD
            8194  => "\xe2\x80\x82", //EN SPACE
            8195  => "\xe2\x80\x83", //EM SPACE
            8196  => "\xe2\x80\x84", //THREE-PER-EM SPACE
            8197  => "\xe2\x80\x85", //FOUR-PER-EM SPACE
            8198  => "\xe2\x80\x86", //SIX-PER-EM SPACE
            8199  => "\xe2\x80\x87", //FIGURE SPACE
            8200  => "\xe2\x80\x88", //PUNCTUATION SPACE
            8201  => "\xe2\x80\x89", //THIN SPACE
            8202  => "\xe2\x80\x8a", //HAIR SPACE
            8232  => "\xe2\x80\xa8", //LINE SEPARATOR
            8233  => "\xe2\x80\xa9", //PARAGRAPH SEPARATOR
            8239  => "\xe2\x80\xaf", //NARROW NO-BREAK SPACE
            8287  => "\xe2\x81\x9f", //MEDIUM MATHEMATICAL SPACE
            12288 => "\xe3\x80\x80", //IDEOGRAPHIC SPACE
        ];

        return $white;
    }

    public static function isUtf8(string $str) {
        $length = mb_strlen($str);
        for ($i = 0; $i < $length; $i++) {
            if (($str[$i] & "\x80") === "\x00") {
                continue;
            } elseif ((($str[$i] & "\xE0") === "\xC0") && (isset($str[$i + 1]))) {
                if (($str[$i + 1] & "\xC0") === "\x80") {
                    $i++;
                    continue;
                }

                return false;
            } elseif ((($str[$i] & "\xF0") === "\xE0") && (isset($str[$i + 2]))) {
                if ((($str[$i + 1] & "\xC0") === "\x80") && (($str[$i + 2] & "\xC0") === "\x80")) {
                    $i = $i + 2;
                    continue;
                }

                return false;
            } elseif ((($str[$i] & "\xF8") === "\xF0") && (isset($str[$i + 3]))) {
                if ((($str[$i + 1] & "\xC0") === "\x80") && (($str[$i + 2] & "\xC0") === "\x80") && (($str[$i + 3] & "\xC0") === "\x80")) {
                    $i += 3;
                    continue;
                }

                return false;
            } else {
                return false;
            }
        }

        return true;
    }

    public static function isUtf8Bom(string $chr) {
        return $chr === self::getUtf8Bom();
    }

    public static function isUtf8Enabled(): bool {
        $result = false;
        if ((preg_match('/./u', 'é') === 1 && function_exists('iconv') && ini_get('mbstring.func_overload') != 1) || mb_internal_encoding() === 'UTF-8') {
            $result = true;
        }

        return $result;
    }
}
