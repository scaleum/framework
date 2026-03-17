<?php
declare(strict_types=1);
/**
 * This file is part of Scaleum Framework.
 *
 * (C) 2009-2026 Maxim Kirichenko <kirichenko.maxim@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Scaleum\Security\Services;

use Scaleum\Stdlib\Exceptions\ERuntimeError;
use Scaleum\Storages\PDO\Database;

final class AclTableGuard
{
    private static array $checked = [];

    private function __construct()
    {
    }

    public static function assertTableExists(Database $database, string $tableName): void
    {
        $key = $database->getSignature() . ':' . $tableName;
        if (isset(self::$checked[$key])) {
            return;
        }

        if (! $database->getSchemaBuilder()->existsTable($tableName)) {
            throw new ERuntimeError(
                sprintf(
                    "ACL table `%s` is not found. Create it via migration before using ACL resource.",
                    $tableName
                )
            );
        }

        self::$checked[$key] = true;
    }
}
