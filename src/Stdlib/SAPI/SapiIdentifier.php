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

namespace Scaleum\Stdlib\SAPI;

use Scaleum\Stdlib\Exceptions\ERuntimeError;

/**
 * SapiIdentifier
 *
 * @author Maxim Kirichenko <kirichenko.maxim@gmail.com>
 */
enum SapiIdentifier: int {
case CLI       = 1;
case PHPDBG    = 2;
case APACHE    = 3;
case CGI       = 4;
case FASTCGI   = 5;
case FPM       = 6;
case LITESPEED = 7;
case ISAPI     = 8;
case EMBED     = 9;
case UWSGI     = 10;
case UNKNOWN   = 100;

    public const NAMES = [
        self::CLI       => 'Command Line Interface',
        self::PHPDBG    => 'PHP Debugger',
        self::APACHE    => 'Apache Handler',
        self::CGI       => 'Common Gateway Interface',
        self::FASTCGI   => 'FastCGI',
        self::FPM       => 'FastCGI Process Manager',
        self::LITESPEED => 'LiteSpeed',
        self::ISAPI     => 'Internet Server API',
        self::EMBED     => 'Embedded PHP',
        self::UWSGI     => 'uWSGI Interface',
        self::UNKNOWN   => 'Unknown',
    ];

    public function getName(): string {
        return self::NAMES[$this->value];
    }

    public static function fromString(string $str): self {
        return match (strtolower($str)) {
            'cli' => self::CLI,
            'cli-server' => self::CLI,
            'phpdbg' => self::PHPDBG,
            'apache2handler' => self::APACHE,
            'cgi' => self::CGI,
            'cgi-fcgi' => self::FASTCGI,
            'fpm-fcgi' => self::FPM,
            'litespeed' => self::LITESPEED,
            'isapi' => self::ISAPI,
            'embed' => self::EMBED,
            'uwsgi' => self::UWSGI,
            default => throw new ERuntimeError("Unknown SAPI type: $str"),
        };
    }

    public static function fromValue(int $value): self {
        return self::from($value);
    }
}
/** End of SapiIdentifier **/