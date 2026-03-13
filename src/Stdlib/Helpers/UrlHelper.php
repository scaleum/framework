<?php

declare(strict_types=1);
/**
 * This file is part of Scaleum\Stdlib.
 *
 * (C) 2009-2025 Maxim Kirichenko <kirichenko.maxim@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Scaleum\Stdlib\Helpers;

class UrlHelper
{
    private const URL_PATTERN     = '/^(?:(?P<protocol>\w+):\/{2})?(?P<hostname>[^\/:]+(?:\.[\w]{2,16})?)(?:\:(?P<port>\d+))?(?P<path>\/(?:[^\/?]+\/?)?)?(?P<file>[^\?#]+)?(?:\?(?P<query>[^#]*))?(?P<hash>\#.*)?$/i';
    private const URL_ALT_PATTERN = '/([a-z0-9_\-]{1,7}:\/\/)?(([a-z0-9_\-]{1,}):([a-z0-9_\-]{1,})\@)?((www\.)|([a-z0-9_\-]{1,}\.)+)?([a-z0-9_\-]{3,})(\.[a-z]{2,8})(\/([a-z0-9_\-]{1,}\/)+)?([a-z0-9_\-]{1,})?(\.[a-z]{2,})?(\?)?(((\&)?[a-z0-9_\-]{1,}(\=[a-z0-9_\-]{1,})?)+)?/i';

    /**
     * Returns the base URL for the application.
     *
     * @param string $url The relative URL to append to the base URL.
     * @return string The complete URL.
     */
    public static function baseUrl(string $url = ''): string
    {
        $baseUrl = self::getServerProtocol() . '://' . self::getServerName() . ":" . self::getServerPort() . "/";
        if ($url === '') {
            return $baseUrl;
        }

        return $baseUrl . ltrim($url, '/');
    }

    public static function getServerPort(): int
    {
        return (int) ArrayHelper::element('SERVER_PORT', $_SERVER, 80);
    }

    /**
     * Returns the server name.
     *
     * @return string The server name.
     */
    public static function getServerName(): string
    {
        $result = rtrim(ArrayHelper::element('HTTP_HOST', $_SERVER, 'localhost'), '/');
        if (strpos($result, ':') !== false) {
            $result = substr($result, 0, strpos($result, ':'));
        }
        return $result;
    }

    /**
     * Returns the server protocol.
     *
     * @return string The server protocol.
     */
    public static function getServerProtocol(): string
    {
        list($https, $port) = ArrayHelper::elements(['HTTPS', 'SERVER_PORT'], $_SERVER, ['off', 80]);

        return ($https !== 'off' || $port == 443) ? "https" : "http";
    }

    /**
     * Once URL is matched capturing groups will contain the following information:
     *
     * Protocol: $1 (e.g. http, ftp, etc.)
     * Hostname: $2 (e.g. www.myhost.com)
     * Port: $3 (e.g. 8080)
     * Path: $4 (e.g. /catalogue/tables)
     * File: $5 (e.g. ProductDetails.aspx)
     * Query string: $6 (e.g. first=1&second=2)
     * Hash: $7 (e.g. #description)
     * @param string $url
     * @return array|bool
     */
    public static function parse(string $url = ''): array | bool
    {
        $url = trim($url);
        if ($url === '') {
            return false;
        }

        if (preg_match(self::URL_PATTERN, $url, $matches) === 1) {
            return $matches;
        }

        return false;
    }

    /**
     * Parses the alternative URL.
     *
     * Protocol: $1
     * User: $3
     * Password: $4
     * Subdomain: $5
     * Domain: $8
     * DomainEnd: $9
     * Path: $10
     * File: $12
     * Filetype: $13
     * GET-Parameters: $15
     *
     * @param string $url The URL to parse.
     * @return array|bool Returns an array if the URL is successfully parsed, otherwise returns false.
     */
    public static function parseAlt(string $url = ''): array | bool
    {
        $url = trim($url);
        if ($url === '') {
            return false;
        }

        if (preg_match(self::URL_ALT_PATTERN, $url, $matches) === 1) {
            return $matches;
        }

        return false;
    }

    /**
     * Redirects the user to a specified URI.
     *
     * @param string $uri The URI to redirect to. Default is an empty string.
     * @param string $method The method to use for redirection. Default is 'location'.
     * @param int $httpResponseCode The HTTP response code to use for redirection. Default is 302.
     * @return void
     */
    public static function redirect(string $uri = '', string $method = 'location', int $httpResponseCode = 302)
    {
        if (! headers_sent()) {
            if (! self::isAbsoluteHttpUrl($uri)) {
                $uri = self::baseUrl($uri);
            }

            switch ($method) {
                case 'refresh':
                    header("Refresh:0;url=$uri");
                    break;
                default:
                    header("Location: $uri", true, $httpResponseCode);
                    break;
            }
        }
    }

    /**
     * Checks if the given string is a valid URL.
     *
     * @param string $value The string to validate as a URL.
     *
     * @return bool True if the value is a valid URL, false otherwise.
     */
    public static function isUrl(string $value): bool
    {
        $value = trim($value);
        if ($value === '') {
            return false;
        }

        return filter_var($value, FILTER_VALIDATE_URL) !== false;
    }

    /**
     * Determines whether a URL is an absolute HTTP(S) URL.
     *
     * @param string $url The URL string to validate.
     * @return bool True if the URL is an absolute HTTP(S) URL, false otherwise.
     */
    private static function isAbsoluteHttpUrl(string $url): bool
    {
        return strncasecmp($url, 'http://', 7) === 0 || strncasecmp($url, 'https://', 8) === 0;
    }
}
