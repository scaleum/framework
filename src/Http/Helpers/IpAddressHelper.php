<?php
declare(strict_types=1);
/**
 * This file is part of Scaleum Framework.
 *
 * (C) 2009-2025 Maxim Kirichenko <kirichenko.maxim@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Avant\Http\Helpers;

/**
 * Class IpAddressHelper
 * Original source: https://phppot.com/php/how-to-get-the-client-user-ip-address-in-php/
 */
class IpAddressHelper
{
    public static function getIpAddress()
    {
        $result = '';
        if (!empty( $_SERVER['HTTP_CLIENT_IP'] )) {
            // to get shared ISP IP address
            $result = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty( $_SERVER['HTTP_X_FORWARDED_FOR'] )) {
            // check for IPs passing through proxy servers
            // check if multiple IP addresses are set and take the first one
            $ipAddressList = explode( ',', $_SERVER['HTTP_X_FORWARDED_FOR'] );
            foreach ($ipAddressList as $ip) {
                if (!empty( $ip )) {
                    // if you prefer, you can check for valid IP address here
                    $result = $ip;
                    break;
                }
            }
        } elseif (!empty( $_SERVER['HTTP_X_FORWARDED'] )) {
            $result = $_SERVER['HTTP_X_FORWARDED'];
        } elseif (!empty( $_SERVER['HTTP_X_CLUSTER_CLIENT_IP'] )) {
            $result = $_SERVER['HTTP_X_CLUSTER_CLIENT_IP'];
        } elseif (!empty( $_SERVER['HTTP_FORWARDED_FOR'] )) {
            $result = $_SERVER['HTTP_FORWARDED_FOR'];
        } elseif (!empty( $_SERVER['HTTP_FORWARDED'] )) {
            $result = $_SERVER['HTTP_FORWARDED'];
        } elseif (!empty( $_SERVER['REMOTE_ADDR'] )) {
            $result = $_SERVER['REMOTE_ADDR'];
        }

        return $result;
    }

    public static function isIpAddress($ip)
    {
        if (filter_var( $ip, FILTER_VALIDATE_IP,
            FILTER_FLAG_IPV4 |
            FILTER_FLAG_IPV6 |
            FILTER_FLAG_NO_PRIV_RANGE |
            FILTER_FLAG_NO_RES_RANGE
          ) === false) {
            return false;
        }

        return true;
    }
}

/* End of file IpAddressHelper.php */
