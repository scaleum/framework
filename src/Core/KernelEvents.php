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

namespace Scaleum\Core;

/**
 * KernelEvents
 *
 * @author Maxim Kirichenko <kirichenko.maxim@gmail.com>
 */
class KernelEvents {
    public const BOOTSTRAP = 'kernel:Bootstrap';
    public const START     = 'kernel:Start';
    public const FINISH    = 'kernel:Finish';
    public const HALT      = 'kernel:Halt';
}
/** End of KernelEvents **/