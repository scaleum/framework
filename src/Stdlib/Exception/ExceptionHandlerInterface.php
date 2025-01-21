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

namespace Scaleum\Stdlib\Exception;

/**
 * ExceptionHandlerInterface
 *
 * @author Maxim Kirichenko <kirichenko.maxim@gmail.com>
 */
interface ExceptionHandlerInterface {
    public function handle(\Throwable $exception): void;

    /**
     * @return ExceptionRendererInterface|callable(\Throwable): void|null
     */
    public function getRenderer(): ExceptionRendererInterface | callable | null;
    /**
     * @param ExceptionRendererInterface|callable(\Throwable): void $renderer
     * @return void
     */
    public function setRenderer(ExceptionRendererInterface | callable $renderer): void;
}
/** End of ExceptionHandlerInterface **/