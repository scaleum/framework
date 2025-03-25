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

namespace Scaleum\Auth\Supports;

use Scaleum\Stdlib\Base\AttributeContainer;

/**
 * JwtTokenPayload
 *
 * @author Maxim Kirichenko <kirichenko.maxim@gmail.com>
 */
class JwtTokenPayload extends AttributeContainer {
    public function getUserId(): int {
        return (int)$this->getAttribute('user_id', 0);
    }
}
/** End of JwtTokenPayload **/