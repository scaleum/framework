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
namespace Scaleum\Security;

final class Permission
{
    public const NONE = 0;

    public const READ = 1 << 0;
    public const WRITE = 1 << 1;
    public const DELETE = 1 << 2;
    public const EXECUTE = 1 << 3;
    public const PRINT = 1 << 4;
    public const EXPORT = 1 << 5;
    public const IMPORT = 1 << 6;
    public const SHARE = 1 << 7;

    public const ALL =
        self::READ |
        self::WRITE |
        self::DELETE |
        self::EXECUTE |
        self::PRINT |
        self::EXPORT |
        self::IMPORT |
        self::SHARE;

    private function __construct()
    {
    }
}