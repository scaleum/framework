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
    public const FORMAT_JSON       = 'json';
    public const FORMAT_JSONP      = 'jsonp';
    public const FORMAT_SERIALIZED = 'serialized';
    public const FORMAT_PHP        = 'php';
    public const FORMAT_HTML       = 'html';
    public const FORMAT_HTM        = 'htm';
    public const FORMAT_XML        = 'xml';
    public const FORMAT_CSV        = 'csv';

    public const ALLOWED_FORMATS = [
        self::FORMAT_HTML,
        self::FORMAT_HTM,
        self::FORMAT_JSON,
        self::FORMAT_JSONP,
        self::FORMAT_SERIALIZED,
        self::FORMAT_PHP,
        self::FORMAT_XML,
        self::FORMAT_CSV,
    ];

    public const ALLOWED_MIME_TYPES = [
        self::FORMAT_HTML       => 'text/html',
        self::FORMAT_HTM        => 'text/html',
        self::FORMAT_JSON       => 'application/json',
        self::FORMAT_JSONP      => 'application/javascript',
        self::FORMAT_SERIALIZED => 'application/vnd.php.serialized',
        self::FORMAT_PHP        => 'text/plain',
        self::FORMAT_XML        => 'application/xml',
        self::FORMAT_CSV        => 'text/csv',
    ];

    public const METHOD_GET           = 'GET';
    public const METHOD_POST          = 'POST';
    public const METHOD_PUT           = 'PUT';
    public const METHOD_PATCH         = 'PATCH';
    public const METHOD_DELETE        = 'DELETE';
    public const METHOD_OPTIONS       = 'OPTIONS';
    public const METHOD_HEAD          = 'HEAD';
    public const ALLOWED_HTTP_METHODS = [
        self::METHOD_GET,
        self::METHOD_POST,
        self::METHOD_PUT,
        self::METHOD_PATCH,
        self::METHOD_DELETE,
        self::METHOD_OPTIONS,
        self::METHOD_HEAD,
    ];

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

    public static function getAllowedMimeTypes():array{
        return array_values(self::ALLOWED_MIME_TYPES);
    }

    public static function getAllowedMimeType(string $format):string{
        return self::ALLOWED_MIME_TYPES[$format] ?? self::ALLOWED_MIME_TYPES[self::FORMAT_HTML];
    }
    
    public static function getAcceptFormat(): string
    {
        // ключ первого доступного формата — запасной вариант
        $defaultFormat = self::FORMAT_HTML;
    
        foreach (['HTTP_ACCEPT', 'HTTP_CONTENT_TYPE', 'CONTENT_TYPE'] as $header) {
            // если заголовок не передан — пропускаем
            if (empty($_SERVER[$header])) {
                continue;
            }
    
            // забираем только первую часть до запятой и в нижнем регистре
            $mime = strtolower(strtok($_SERVER[$header], ','));
    
            // ищем, есть ли такой MIME в наших форматах
            $found = array_search($mime, self::ALLOWED_MIME_TYPES, true);
            if ($found !== false) {
                return $found;
            }
        }
    
        return $defaultFormat;
    }
    
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
                self::setHeader("HTTP/1.1 {$code} {$text}", replace: true, responseCode: $code);
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

    public static function isMethod(string $method): bool {
        return in_array($method, self::ALLOWED_HTTP_METHODS);
    }

    public static function getUserIP(): string {
        $result = '';
        if (! empty($_SERVER['HTTP_CLIENT_IP'])) {
            // to get shared ISP IP address
            $result = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (! empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            // check for IPs passing through proxy servers
            // check if multiple IP addresses are set and take the first one
            $ipAddressList = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            foreach ($ipAddressList as $ip) {
                if (! empty($ip)) {
                    // if you prefer, you can check for valid IP address here
                    $result = $ip;
                    break;
                }
            }
        } elseif (! empty($_SERVER['HTTP_X_FORWARDED'])) {
            $result = $_SERVER['HTTP_X_FORWARDED'];
        } elseif (! empty($_SERVER['HTTP_X_CLUSTER_CLIENT_IP'])) {
            $result = $_SERVER['HTTP_X_CLUSTER_CLIENT_IP'];
        } elseif (! empty($_SERVER['HTTP_FORWARDED_FOR'])) {
            $result = $_SERVER['HTTP_FORWARDED_FOR'];
        } elseif (! empty($_SERVER['HTTP_FORWARDED'])) {
            $result = $_SERVER['HTTP_FORWARDED'];
        } elseif (! empty($_SERVER['REMOTE_ADDR'])) {
            $result = $_SERVER['REMOTE_ADDR'];
        }

        return $result;
    }

    public static function isIpAddress($ip) {
        if (filter_var($ip, FILTER_VALIDATE_IP,
            FILTER_FLAG_IPV4 |
            FILTER_FLAG_IPV6 |
            FILTER_FLAG_NO_PRIV_RANGE |
            FILTER_FLAG_NO_RES_RANGE
        ) === false) {
            return false;
        }

        return true;
    }

    public static function getUserAgent(string $default = 'Unknown'): string {
        $headers = [
            // The default User-Agent string.
            'HTTP_USER_AGENT',
            // Header can occur on devices using Opera Mini.
            'HTTP_X_OPERAMINI_PHONE_UA',
            // Vodafone specific header: http://www.seoprinciple.com/mobile-web-community-still-angry-at-vodafone/24/
            'HTTP_X_DEVICE_USER_AGENT',
            'HTTP_X_ORIGINAL_USER_AGENT',
            'HTTP_X_SKYFIRE_PHONE',
            'HTTP_X_BOLT_PHONE_UA',
            'HTTP_DEVICE_STOCK_UA',
            'HTTP_X_UCBROWSER_DEVICE_UA',
        ];

        $str = '';
        foreach ($headers as $header) {
            if (isset($_SERVER[$header]) && ! empty($_SERVER[$header])) {
                $str .= $_SERVER[$header] . ' ';
            }
        }

        return trim(empty($str) ? $default : substr($str, 0, 512));
    }
}
/** End of HttpHelper **/