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

use ErrorException;
use Scaleum\Stdlib\Helpers\ArrayHelper;
use Scaleum\Stdlib\Helpers\HttpHelper;
use Scaleum\Stdlib\Helpers\PathHelper;
use Scaleum\Stdlib\Helpers\StringHelper;

/**
 * RenderHttp
 *
 * @author Maxim Kirichenko <kirichenko.maxim@gmail.com>
 */
class ExceptionOutputHttp extends ExceptionOutputAbstarct {
    protected int $statusCode = 500;

    public function render(\Throwable $exception): void {
        HttpHelper::setHeader('Content-Type', sprintf('%s; charset=utf-8', HttpHelper::getAllowedMimeType($format = HttpHelper::getAcceptFormat())));
        HttpHelper::setStatusHeader(
            $this->statusCode = HttpHelper::isStatusCode(
                $code = $exception instanceof ErrorException ? ($exception instanceof EBasicException ? $exception->getCode() : $exception->getSeverity()) : $exception->getCode()
            ) ? $code : 500
        );
        echo $this->formatException($exception, $format);
    }

    protected function formatException(\Throwable $exception, ?string $format = null): string {
        if ($format == null) {
            $format = HttpHelper::getAcceptFormat();
        }

        $result = $exception->getMessage();
        switch ($format) {
        case HttpHelper::FORMAT_JSON:
        case HttpHelper::FORMAT_JSONP:
            $result = $this->formatAsJson($exception);
            break;
        case HttpHelper::FORMAT_SERIALIZED:
            $result = $this->formatAsSerializable($exception);
            break;
        case HttpHelper::FORMAT_XML:
            $result = $this->formatAsXml($exception);
            break;
        case HttpHelper::FORMAT_PHP:
        case HttpHelper::FORMAT_HTML:
        case HttpHelper::FORMAT_HTM:
        default:
            $result = $this->formatAsHtml($exception);
        }
        return $result;
    }

    protected function formatAsSerializable(\Throwable $exception): string {
        return ArrayHelper::castToSerialize($this->errorToArray($exception));
    }

    protected function formatAsJson(\Throwable $exception): string {
        return json_encode($this->errorToArray($exception), JSON_PRETTY_PRINT);
    }

    protected function formatAsXml(\Throwable $exception): string {
        return ArrayHelper::castToXml($this->errorToArray($exception), 'exception');
    }

    protected function formatAsHtml(\Throwable $exception): string {
        $statusCode    = $this->statusCode;
        $statusMessage = HttpHelper::getStatusMessage($statusCode);

        $encode = function (array $array, ?int $level = 0) use (&$encode): string {
            $result = '<ul style="list-style-type:none; background-color: #f9f9f9; padding: 8px; font-family: sans-serif, monospace; font-size: 11px;">';
            foreach ($array as $key => $value) {
                $result .= '<li>';
                $result .= '<strong>' . htmlspecialchars((string) $key) . ':</strong>&nbsp;';
                if (is_array($value)) {
                    $result .= $encode($value, ++$level);
                } else {
                    $result .= htmlspecialchars((string) $value);
                }
                $result .= '</li>';
            }
            $result .= '</ul>';

            return $result;
        };

        $result = "<!DOCTYPE html><html><head><title>HTTP Error {$statusCode} - {$statusMessage}</title></head><body>";
        $result .= "<h2>HTTP Error {$statusCode} - {$statusMessage}</h2>";
        $result .= '<h3 style="color:red">' . $exception->getMessage() . '</h3>';
        if ($this->includeDetails) {
            $result .= '<div>' . $encode($this->errorToArray($exception)) . '</div>';
        }
        $result .= '</body></html>';
        return $result;
    }

    protected function errorToArray(\Throwable $exception): array {
        # Prepare result
        $result = [
            'class'   => StringHelper::className($exception, ! $this->allowFullnamespace) . '(' . $exception->getCode() . ')',
            'message' => $exception->getMessage(),
            'file'    => PathHelper::overlapPath($exception->getFile(), $this->basePath) . ':' . $exception->getLine(),
        ];

        # Add trace
        if ($this->includeTraces) {
            $result['trace'] = $this->errorTraceToArray($exception->getTrace());
        }

        # Add info about previous
        if (($previous = $exception->getPrevious()) instanceof \Throwable) {
            $result['previous'] = $this->errorToArray($previous);
        }

        return $result;
    }

    protected function errorTraceToArray(array $trace): array {
        return array_map(function ($item) {
            $result = '';
            $result .= $item['class'] ? StringHelper::className($item['class'], ! $this->allowFullnamespace) : '';
            $result .= $item['type'] ?: "";
            $result .= $item['function'] ? "{$item['function']}()" : '';
            $result .= $item['file'] ? " in " . PathHelper::overlapPath($item['file'], $this->basePath) . ($item['line'] ? ":{$item['line']}" : '') : '';

            return $result;
        }, $trace);
    }

}
/** End of RenderHttp **/