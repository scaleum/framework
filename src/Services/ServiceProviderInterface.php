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

namespace Scaleum\Services;

/**
 * ServiceProviderInterface
 *
 * @author Maxim Kirichenko <kirichenko.maxim@gmail.com>
 */
interface ServiceProviderInterface {
    public function getAll(): array;
    public function getService(string $str, mixed $default = null): mixed;
    public function hasService(string $str): bool;
    public function setService(string $str, mixed $definition, bool $override = false): mixed;
}
/** End of ServiceProviderInterface **/