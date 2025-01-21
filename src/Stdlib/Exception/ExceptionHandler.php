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
 * ExceptionHandler
 *
 * @author Maxim Kirichenko <kirichenko.maxim@gmail.com>
 */
class ExceptionHandler implements ExceptionHandlerInterface {
    /**
     * @var ExceptionRendererInterface|callable(\Throwable): void|null
     */
    protected mixed $renderer = null;

    public function handle(\Throwable $exception): void {        
        if ($this->renderer instanceof ExceptionRendererInterface) {
            $this->renderer->render($exception);
        } elseif (is_callable($this->renderer)) {
            ($this->renderer)($exception);
        } else {
            echo 'An error occurred: ' . $exception->getMessage();
        }
    }

    /**
     * @return ExceptionRendererInterface|callable(\Throwable): void|null
     */
    public function getRenderer(): ExceptionRendererInterface | callable | null {
        return $this->renderer;
    }
    /**
     * @param ExceptionRendererInterface|callable(\Throwable): void $renderer
     * @return void
     */
    public function setRenderer(ExceptionRendererInterface | callable $renderer): void {
        $this->renderer = $renderer;
    }
}
/** End of ExceptionHandler **/