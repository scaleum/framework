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

use Scaleum\DependencyInjection\Contract\ResolvableReference;

/**
 * Reference
 *
 * @author Maxim Kirichenko <kirichenko.maxim@gmail.com>
 */
class Reference extends EntityAbstract {
    private string $id;

    public function __construct(string $id) {
        $this->id = $id; 
    }

    public function getId(): string {
        return $this->id;
    }
}
/** End of Reference **/