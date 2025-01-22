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

use ErrorException;
use Scaleum\Stdlib\Helper\HttpHelper;
use Scaleum\Stdlib\Helper\PathHelper;
use Scaleum\Stdlib\Helper\StringHelper;

/**
 * RenderHttp
 *
 * @author Maxim Kirichenko <kirichenko.maxim@gmail.com>
 */
class ExceptionOutputHttp extends ExceptionOutputAbstarct {
    public const FORMAT_JSON       = 'json';
    public const FORMAT_JSONP      = 'jsonp';
    public const FORMAT_SERIALIZED = 'serialized';
    public const FORMAT_PHP        = 'php';
    public const FORMAT_HTML       = 'html';
    public const FORMAT_HTM        = 'htm';
    public const FORMAT_XML        = 'xml';

    protected array $formats = [
        self::FORMAT_HTML       => 'text/html',
        self::FORMAT_HTM        => 'text/html',
        self::FORMAT_JSON       => 'application/json',
        self::FORMAT_JSONP      => 'application/javascript',
        self::FORMAT_SERIALIZED => 'application/vnd.php.serialized',
        self::FORMAT_PHP        => 'text/plain',
        self::FORMAT_XML        => 'application/xml',
    ];

    public function render(\Throwable $exception): void {
        HttpHelper::setHeader('Content-Type', sprintf('%s; charset=utf-8', $this->formats[$format = $this->getResponseFormat()]));
        HttpHelper::setStatusHeader(
            HttpHelper::isHttpStatus(
                $code = $exception instanceof ErrorException ? $exception->getSeverity() : $exception->getCode()
            ) ? $code : 500
        );
        echo $this->formatException($exception, $format);
    }

    protected function getResponseFormat(): string {
        /** Detect response type */
        $types = [];
        foreach (['HTTP_ACCEPT', 'CONTENT_TYPE'] as $header) {
            if (isset($_SERVER[$header])) {
                $type = strtolower($_SERVER[$header]);
                if (strpos($type, ',')) {
                    $type = current(explode(',', $type));
                }
                $types[] = trim($type);
            }
        }

        // Default 'html' where $mimeType == '*/*'
        $result = key($this->formats);
        foreach ($this->formats as $key => $mimeType) {
            foreach ($types as $type) {
                if ($type == $mimeType) {
                    $result = $key;
                    break 2;
                }
            }
        }

        return $result;
    }

    protected function formatException(\Throwable $exception, ?string $format = null): string {
        if ($format == null) {
            $format = $this->getResponseFormat();
        }

        $result = $exception->getMessage();
        switch ($format) {
        case self::FORMAT_JSON:
        case self::FORMAT_JSONP:
            $result = $this->formatAsJson($exception);
            break;
        case self::FORMAT_SERIALIZED:
            $result = $this->formatAsSerializable($exception);
            break;
        case self::FORMAT_XML:
            $result = $this->formatAsXml($exception);
            break;
        case self::FORMAT_PHP:
        case self::FORMAT_HTML:
        case self::FORMAT_HTM:
        default:
            $result = $this->formatAsHtml($exception);
        }
        return $result;
    }

    protected function formatAsSerializable(\Throwable $exception): string {
        return serialize($this->errorToArray($exception));
    }

    protected function formatAsJson(\Throwable $exception): string {
        return json_encode($this->errorToArray($exception), JSON_PRETTY_PRINT);
    }

    protected function formatAsXml(\Throwable $exception): string {
        $xml = new \SimpleXMLElement('<exception/>');
        $this->xml_encode($this->errorToArray($exception), $xml);
        return $xml->asXML();
    }

    protected function formatAsHtml(\Throwable $exception): string {
        $code = HttpHelper::isHttpStatus(
            $code = $exception instanceof ErrorException ? $exception->getSeverity() : $exception->getCode()
        ) ? $code : 500;

        $code_str = HttpHelper::getStatusName($code);

        $result = "<!DOCTYPE html><html><head><title>HTTP Error {$code} - {$code_str}</title></head><body>";
        $result .= "<h1>HTTP Error {$code} - {$code_str}</h1>";
        $result .= '<h3 style="color:red">' . $exception->getMessage() . '</h3>';
        $result .= '<div>' . $this->html_encode($this->errorToArray($exception)) . '</div>';
        $result .= '</body></html>';
        return $result;
    }

    protected function errorToArray(\Throwable $exception): array {
        # Prepare result
        $result = [
            'class'   => StringHelper::className($exception, ! $this->allow_fullnamespace) . '(' . $exception->getCode() . ')',
            'message' => $exception->getMessage(),
            'file'    => PathHelper::overlapPath($exception->getFile(), $this->base_path) . ':' . $exception->getLine(),
        ];

        # Add trace
        if ($this->include_traces) {
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
            // return [
            //     'class'    => $item['class'] ? StringHelper::className($item['class'], ! $this->allow_fullnamespace) : null,
            //     'function' => $item['function'] ?? null,
            //     'file'     => $item['file'] ? PathHelper::overlapPath($item['file'], $this->base_path) . ($item['line'] ? ":{$item['line']}" : '') : null,
            //     'type'     => $traceItem['type'] ?? null,
            // ];

            $result = '';
            $result .= $item['class'] ? StringHelper::className($item['class'], ! $this->allow_fullnamespace) : '';
            $result .= $item['type'] ?: "::";
            $result .= $item['function'] ? "{$item['function']}()" : '';
            $result .= $item['file'] ? " in " . PathHelper::overlapPath($item['file'], $this->base_path) . ($item['line'] ? ":{$item['line']}" : '') : '';

            return $result;
        }, $trace);
    }

    private function html_encode(array $array, ?int $level = 0): string {
        $html = '<ul style="list-style-type:none; background-color: #f9f9f9; padding: 8px; font-family: sans-serif, monospace; font-size: 11px;">';
        foreach ($array as $key => $value) {
            $html .= '<li>';
            $html .= '<strong>' . htmlspecialchars((string) $key) . ':</strong>&nbsp;';
            if (is_array($value)) {
                $html .= $this->html_encode($value, ++$level);
            } else {
                $html .= htmlspecialchars((string) $value);
            }
            $html .= '</li>';
        }
        $html .= '</ul>';

        return $html;
    }

    private function xml_encode(array $array, \SimpleXMLElement $xmlElement): void {
        foreach ($array as $key => $value) {
            $key = is_numeric($key) ? 'item' : $key;
            if (is_array($value)) {
                $child = $xmlElement->addChild($key);
                $this->xml_encode($value, $child);
            } else {
                $xmlElement->addChild($key, htmlspecialchars((string) $value));
            }
        }
    }
}
/** End of RenderHttp **/