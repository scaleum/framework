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
 * StringCaseHelper
 *
 * Примеры использования
 * echo StringCaseHelper::splitString('pathToFolder'); // path.to.folder
 * echo StringCaseHelper::splitString('path_to_folder'); // path.to.folder
 * echo StringCaseHelper::splitString('PathToFolder'); // path.to.folder

 * Использование другого разделителя
 * echo StringCaseHelper::splitString('pathToFolder', '-'); // path-to-folder
 * echo StringCaseHelper::splitString('path_to_folder', '|'); // path|to|folder
 * echo StringCaseHelper::splitString('PathToFolder', '_'); // path_to_folder
 */
class StringCaseHelper {
    public static function camelize($str) {
        $str = 'x' . strtolower(trim($str));
        $str = ucwords(preg_replace('/[\s_]+/', ' ', $str));

        return substr(str_replace(' ', '', $str), 1);
    }

    public static function humanize($str) {
        return ucwords(preg_replace('/[_]+/', ' ', strtolower(trim($str))));
    }

    // Проверка на camelCase
    public static function isCamelCase(string $string) {
        return preg_match('/^[a-z]+([A-Z][a-z]*)+$/', $string) === 1;
    }

    // Проверка на snake_case
    public static function isSnakeCase(string $string) {
        return preg_match('/^[a-z]+(_[a-z]+)+$/', $string) === 1;
    }

    // Проверка на PascalCase
    public static function isPascalCase(string $string) {
        return preg_match('/^[A-Z][a-z]*([A-Z][a-z]*)+$/', $string) === 1;
    }

    // Разбиение строки на части с указанием разделителя (по умолчанию ".")
    public static function splitString(string $string, string $delimiter = '.') {
        if (self::isCamelCase($string)) {
            // camelCase
            return strtolower(preg_replace('/([a-z])([A-Z])/', "\$1$delimiter\$2", $string));
        } elseif (self::isSnakeCase($string)) {
            // snake_case
            return str_replace('_', $delimiter, $string);
        } elseif (self::isPascalCase($string)) {
            // PascalCase
            return strtolower(preg_replace('/([a-z])([A-Z])/', "\$1$delimiter\$2", lcfirst($string)));
        } else {
            // Unknown case
            return $string;
        }
    }
}
