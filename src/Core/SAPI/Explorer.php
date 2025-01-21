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

namespace Scaleum\Core\SAPI;

/**
 * Explorer
 *
 * @author Maxim Kirichenko <kirichenko.maxim@gmail.com>
 */
class Explorer {
    protected static ?SapiIdentifier $type = null;

    public static function getTypeFamily(?SapiIdentifier $type = null): SapiMode {
        return match ($type ?? static::getType()) {
            SapiIdentifier::CLI,
            SapiIdentifier::PHPDBG => SapiMode::CONSOLE,
            SapiIdentifier::APACHE,
            SapiIdentifier::CGI,
            SapiIdentifier::FASTCGI,
            SapiIdentifier::FPM,
            SapiIdentifier::LITESPEED,
            SapiIdentifier::ISAPI,
            SapiIdentifier::UWSGI => SapiMode::HTTP,
            SapiIdentifier::EMBED => SapiMode::UNIVERSAL,
            default => SapiMode::UNKNOWN,
        };
    }

    /**
     * Get the value of mode
     */
    public static function getType() {
        if (static::$type === null) {
            static::$type = SapiIdentifier::fromString(php_sapi_name());
        }
        return static::$type;
    }

    /**
     * Set the value of mode
     *
     * @return  self
     */
    public static function setType(SapiIdentifier $type): void {
        static::$type = $type;
    }
}
/** End of Explorer **/