<?php
declare(strict_types=1);
/**
 * This file is part of Scaleum Framework.
 *
 * (C) 2009-2025 Maxim Kirichenko <kirichenko.maxim@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Scaleum\Session;


/**
 * SessionInterface
 *
 * @author Maxim Kirichenko <kirichenko.maxim@gmail.com>
 */
interface SessionInterface
{
    public function get(int | string $var, mixed $default = false): mixed;
    public function has(int | string $var): bool;
    public function set(int | string $var, mixed $value = null, bool $updateImmediately = true):static;
}
/** End of SessionInterface **/