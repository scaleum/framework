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
namespace Scaleum\Stdlib\Helper;

class XmlHelper
{
    /**
     * Checks if a given string is a valid XML.
     *
     * @param string $str The string to check.
     * @return bool Returns true if the string is a valid XML, false otherwise.
     */
    public static function isXml(string $str)
    {
        $result = false;
        libxml_use_internal_errors( true );
        if (($xml = simplexml_load_string( $str )) !== false) {
            $result = true;
        }
        libxml_clear_errors();

        return $result;
    }
}

/* End of file XmlHelper.php */
