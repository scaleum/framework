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

namespace Scaleum\Stdlib\Exception;


/**
 * ExceptionRendererInterface
 *
 * @author Maxim Kirichenko <kirichenko.maxim@gmail.com>
 */
interface ExceptionRendererInterface
{
    /**
     * Renders an exception.
     *
     * @param \Throwable $exception The exception to render.
     * @return string The rendered exception as a string.
     */
    public function render(\Throwable $exception): void;
}
/** End of ExceptionRendererInterface **/