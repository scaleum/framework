<?php
declare (strict_types = 1);
/**
 * This file is part of Scaleum Framework.
 *
 * (C) 2009-2026 Maxim Kirichenko <kirichenko.maxim@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Scaleum\Security;

final class SubjectType {
    public const USER    = 1;
    public const GROUP   = 2;
    public const ROLE    = 3;

    private function __construct() {
    }
}