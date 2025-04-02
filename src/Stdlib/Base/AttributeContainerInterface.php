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

namespace Scaleum\Stdlib\Base;


/**
 * AttributeContainerInterface
 *
 * @author Maxim Kirichenko <kirichenko.maxim@gmail.com>
 */
interface AttributeContainerInterface
{
    public function getAttribute(string $key, mixed $default = null): mixed;
    public function hasAttribute(string $key): bool;
    public function setAttribute(string $key, mixed $value = null): static;
    public function deleteAttribute(string $key): static;
    public function getAttributes(): array;
    public function setAttributes(array $value): static;
}
/** End of AttributeContainerInterface **/