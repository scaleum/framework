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

namespace Scaleum\Stdlib\Helpers;

use Scaleum\Stdlib\SAPI\Explorer;
use Scaleum\Stdlib\SAPI\SapiIdentifier;

/**
 * HttpHelper
 *
 * @author Maxim Kirichenko <kirichenko.maxim@gmail.com>
 */
class HttpHelper {
    public const METHOD_GET     = 'GET';
    public const METHOD_POST    = 'POST';
    public const METHOD_PUT     = 'PUT';
    public const METHOD_PATCH   = 'PATCH';
    public const METHOD_DELETE  = 'DELETE';
    public const METHOD_OPTIONS = 'OPTIONS';
    public const METHOD_HEAD    = 'HEAD';    
    public static $statuses = [
        100 => 'Continue',
        101 => 'Switching Protocols',
        102 => 'Processing',
        103 => 'Early Hints',
        118 => 'Connection timed out',
        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        203 => 'Non-Authoritative',
        204 => 'No Content',
        205 => 'Reset Content',
        206 => 'Partial Content',
        207 => 'Multi-Status',
        208 => 'Already Reported',
        210 => 'Content Different',
        226 => 'IM Used',
        300 => 'Multiple Choices',
        301 => 'Moved Permanently',
        302 => 'Found',
        303 => 'See Other',
        304 => 'Not Modified',
        305 => 'Use Proxy',
        306 => 'Reserved',
        307 => 'Temporary Redirect',
        308 => 'Permanent Redirect',
        310 => 'Too many Redirect',
        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'Request Time-out',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Request Entity Too Large',
        414 => 'Request-URI Too Long',
        415 => 'Unsupported Media Type',
        416 => 'Requested range unsatisfiable',
        417 => 'Expectation failed',
        418 => "I'm a teapot",
        422 => 'Unprocessable entity',
        423 => 'Locked',
        424 => 'Method failure',
        425 => 'Unordered Collection',
        426 => 'Upgrade Required',
        428 => 'Precondition Required',
        429 => 'Too Many Requests',
        431 => 'Request Header Fields Too Large',
        449 => 'Retry With',
        450 => 'Blocked by Windows Parental Controls',
        451 => 'Unavailable For Legal Reasons',
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway or Proxy Error',
        503 => 'Service Unavailable',
        504 => 'Gateway Time-out',
        505 => 'HTTP Version not supported',
        507 => 'Insufficient storage',
        508 => 'Loop Detected',
        509 => 'Bandwidth Limit Exceeded',
        510 => 'Not Extended',
        511 => 'Network Authentication Required',
    ];

    /**
     * Sets an HTTP header.
     *
     * @param string $header The header string.
     * @param string|null $text The optional text to include with the header.
     * @param bool $replace Whether to replace a previous similar header, or add a second header of the same type.
     * @param int $responseCode The optional response code to send with the header.
     *
     * @return void
     */
    public static function setHeader(string $header, ?string $text = null, bool $replace = true, int $responseCode = 0): void {
        if (! headers_sent()) {
            switch ($text) {
            case null:
                header($header, $replace, $responseCode);
                break;
            default:
                header("{$header}: {$text}", $replace, $responseCode);
                break;
            }
        }
    }

    /**
     * Sets the HTTP status header.
     *
     * @param int $code The HTTP status code.
     * @param string|null $text Optional. The HTTP status text. If not provided, the default status text for the given code will be used.
     * @return void
     */
    public static function setStatusHeader(int $code, ?string $text = null): void {
        if (! headers_sent()) {
            $code = self::isStatusCode($code) ? $code : 200;
            if ($text == null) {
                $text = self::getStatusMessage($code);
            }
            $serverProtocol = $_SERVER['SERVER_PROTOCOL'] ?? false;

            if (Explorer::getType() == SapiIdentifier::CGI) {
                self::setHeader("Status", "{$code} {$text}");
            } elseif ($serverProtocol == 'HTTP/1.1' || $serverProtocol == 'HTTP/1.0') {
                self::setHeader($serverProtocol . " {$code} {$text}", replace: true, responseCode: $code);
            } else {
                self::setHeader("HTTP/1.1 {$code} {$text}", replace: true,  responseCode: $code);
            }
        }
    }

    /**
     * Get the HTTP status message corresponding to a given status code.
     *
     * @param int $code The HTTP status code.
     * @return string The corresponding status message.
     */
    public static function getStatusMessage(int $code): string {
        return self::$statuses[$code] ?? 'Unknown';
    }

    /**
     * Checks if the given status code is a valid HTTP status code.
     *
     * @param int $code The HTTP status code to check.
     * @return bool True if the status code is valid, false otherwise.
     */
    public static function isStatusCode(int $code): bool {
        return isset(self::$statuses[$code]);
    }

}
/** End of HttpHelper **/