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

namespace Scaleum\DependencyInjection\Helpers;

/**
 * Autowire
 *
 * @author Maxim Kirichenko <kirichenko.maxim@gmail.com>
 */
class Autowire extends EntityAbstract {
    public function __construct(private ?string $class = null) {
    }
    public function getClass(): ?string {
        return $this->class;
    }
}
/** End of Autowire **/