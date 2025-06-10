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
use Scaleum\Stdlib\Helpers\JsonHelper;
use Scaleum\Stdlib\Helpers\StringHelper;

/**
 * DatabaseHelper
 *
 * @author Maxim Kirichenko <kirichenko.maxim@gmail.com>
 */
class DatabaseHelper {
    public static function quote(PDO $pdo, mixed $value): string {
        return match (true) {
            $value === NULL => 'NULL',
            is_int($value) => (string) $value,
            is_float($value) => (string) $value,
            is_bool($value) => self::handleBoolean($value, $pdo),
            is_string($value) => self::handleString($value, $pdo),
            is_object($value) => self::handleObject($value, $pdo),
            is_resource($value) => throw new EDatabaseError(sprintf('Resource of type `%s` could not be converted to string', get_resource_type($value))),
            is_array($value) => throw new EDatabaseError(sprintf('Array of type `%s` could not be converted to string', gettype($value))),
            default => throw new EDatabaseError(sprintf('Unsupported type `%s`', gettype($value))),
        };
    }

    public static function quoteLiteral(PDO $pdo, string $value): string {
        $driver = strtolower($pdo->getAttribute(PDO::ATTR_DRIVER_NAME));
        if (in_array($driver, ['mysql', 'sqlite', 'sqlsrv'], true)) {
            $escaped = str_replace("'", "''", $value);
        } elseif ($driver === 'pgsql') {
            $escaped = str_contains($value, '\\')
            ? addcslashes($value, "\\'")
            : str_replace("'", "''", $value);
        } else {
            throw new EDatabaseError("Unsupported PDO driver: $driver");
        }

        // Wrap in quotes with dialect consideration
        return match ($driver) {
            'mysql', 'sqlite' => "'$escaped'",
            'sqlsrv'          => "N'$escaped'",
            'pgsql'           => str_contains($value, '\\') ? "E'$escaped'" : "'$escaped'",
        };
    }

    private static function handleBoolean(bool $value, PDO $pdo): string {
        $driver = strtolower($pdo->getAttribute(PDO::ATTR_DRIVER_NAME));
        return match ($driver) {
            'pgsql'           => $value ? 'TRUE' : 'FALSE', // PostgreSQL requires TRUE/FALSE
            default           => $value ? '1' : '0',        // MySQL, SQLite, SQL Server, Oracle — 1/0
        };
    }

    private static function handleObject(object $value, PDO $pdo): string {
        if (! method_exists($value, '__toString')) {
            throw new EDatabaseError(sprintf('Object of class `%s` could not be converted to string', get_class($value)));
        }
        return $pdo->quote((string) $value, PDO::PARAM_STR);
    }

    private static function handleString(string $value, PDO $pdo): string {
        // String is JSON or serialized data
        if (JsonHelper::isJson($value) || StringHelper::isSerialized($value)) {
            return self::quoteLiteral($pdo, $value);
        }

        // For other cases, just quote the string
        return $pdo->quote(trim($value, "'\""), PDO::PARAM_STR);
    }
}
/** End of DatabaseHelper **/