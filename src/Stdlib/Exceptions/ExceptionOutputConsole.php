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

namespace Scaleum\Stdlib\Exceptions;

use Scaleum\Stdlib\Helpers\ArrayHelper;
use Scaleum\Stdlib\Helpers\PathHelper;
use Scaleum\Stdlib\Helpers\StringHelper;

/**
 * RendererConsole
 *
 * @author Maxim Kirichenko <kirichenko.maxim@gmail.com>
 */
class ExceptionOutputConsole extends ExceptionOutputAbstarct {
    public function render(\Throwable $exception): void {
        $console = @fopen("php://stdout", "w");
        @fwrite($console, $this->formatException($exception));
        @fclose($console);
    }

    /**
     * Formalizes an exception into a string representation.
     *
     * @param \Throwable $exception The exception to be formalized.
     * @return string The formalized string representation of the exception.
     */
    public function formatException(\Throwable $exception, ?int $level = 0): string {

        $result = PHP_EOL;
        $result .= str_pad($level ? "[Previous {$level}]" : "[Error]", 64, '-', STR_PAD_RIGHT) . PHP_EOL;
        $result .= str_pad("Class: ", 10, ' ', STR_PAD_RIGHT) . StringHelper::className($exception, ! $this->allowFullnamespace) . '(' . $exception->getCode() . ')' . PHP_EOL;
        $result .= str_pad("Message: ", 10, ' ', STR_PAD_RIGHT) . $exception->getMessage() . PHP_EOL;
        if ($this->includeDetails) {
            $result .= str_pad("File: ", 10, ' ', STR_PAD_RIGHT) . PathHelper::overlapPath($exception->getFile(), $this->basePath) . ':' . $exception->getLine() . PHP_EOL;
        }

        if ($this->includeTraces) {
            $result .= str_pad("[Backtrace]", 64, '-', STR_PAD_RIGHT) . PHP_EOL;
            $result .= $this->formatTrace($exception->getTrace());
        }

        if (($previous = $exception->getPrevious()) instanceof \Throwable) {
            $result .= $this->formatException($previous, ++$level);
        }

        return $result.PHP_EOL;
    }

    /**
     * Formalizes the given trace array.
     *
     * @param array $trace The trace array to be formalized.
     * @return string The formalized trace.
     */
    public function formatTrace(array $trace): string {
        $result = "";
        $pad    = count($trace) + 1;
        foreach ($trace as $key => $value) {
            $result .= sprintf("\t#%-{$pad}d", $key);

            if (($class = ArrayHelper::element('class', $value))) {
                $result .= StringHelper::className($class, ! $this->allowFullnamespace);
                $result .= ArrayHelper::element('type', $value, '::');
            }

            if ($function = ArrayHelper::element('function', $value)) {
                $result .= "$function()";
            }

            if ($this->includeDetails) {
                if ($file = ArrayHelper::element('file', $value)) {
                    $result .= ' in ' . PathHelper::overlapPath($file, $this->basePath);
                    if ($line = ArrayHelper::element('line', $value)) {
                        $result .= ":$line";
                    }
                }
            }

            $result .= PHP_EOL;
        }
        return $result;
    }
}
/** End of RendererConsole **/