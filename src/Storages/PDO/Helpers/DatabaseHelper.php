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

namespace Scaleum\Storages\PDO\Helpers;

use PDO;
use Scaleum\Stdlib\Exceptions\EDatabaseError;

/**
 * DatabaseHelper
 *
 * @author Maxim Kirichenko <kirichenko.maxim@gmail.com>
 */
class DatabaseHelper {
    public static function quote(PDO $pdo, mixed $value): string {
        $driver = strtolower($pdo->getAttribute(PDO::ATTR_DRIVER_NAME)); // Получаем тип БД
        return match (true) {
            $value === NULL => 'NULL',
            is_int($value) => (string) $value,
            is_float($value) => (string) $value,
            is_bool($value) => self::formatBoolean($value, $driver),
            is_string($value) => $pdo->quote(trim($value, "'\""), PDO::PARAM_STR),
            is_object($value) => self::handleObject($value, $pdo),
            is_resource($value) => throw new EDatabaseError(sprintf('Resource of type `%s` could not be converted to string', get_resource_type($value))),
            is_array($value) => throw new EDatabaseError(sprintf('Array of type `%s` could not be converted to string', gettype($value))),
            default => throw new EDatabaseError(sprintf('Unsupported type `%s`', gettype($value))),
        };
    }

    private static function formatBoolean(bool $value, string $driver): string {
        return match ($driver) {
            'pgsql' => $value ? 'TRUE' : 'FALSE', // PostgreSQL требует TRUE/FALSE
            default => $value ? '1' : '0',        // MySQL, SQLite, SQL Server, Oracle — 1/0
        };
    }

    private static function handleObject(object $value, PDO $pdo): string {
        if (! method_exists($value, '__toString')) {
            throw new EDatabaseError(sprintf('Object of class `%s` could not be converted to string', get_class($value)));
        }
        return $pdo->quote((string) $value, PDO::PARAM_STR);
    }
}
/** End of DatabaseHelper **/