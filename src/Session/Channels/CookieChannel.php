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

namespace Scaleum\Session\Channels;

use Scaleum\Http\InboundRequest;
use Scaleum\Http\OutboundResponse;
use Scaleum\Session\Contracts\SessionChannelInterface;
use Scaleum\Stdlib\Base\Hydrator;



/**
 * CookieSessionChannel
 *
 * @author Maxim Kirichenko <kirichenko.maxim@gmail.com>
 */
class CookieChannel extends Hydrator implements SessionChannelInterface
{
    protected string $keyName = 'SID';
    public function fetchFromRequest(InboundRequest $request): ?string
    {
        $cookies = $request->getCookieParams();
        if (!empty($cookies[$this->keyName])) {
            return rawurldecode((string) $cookies[$this->keyName]);
        }

        return null;
    }

    public function writeToResponse(OutboundResponse $response, string $id, ?int $ttl = null): void
    {
        $value = rawurlencode($id);

        $attrs = [
            'Path=/',
            'HttpOnly',
            'Secure',
            'SameSite=Lax',
        ];

        if ($ttl > 0) {
            $expires = gmdate('D, d M Y H:i:s', time() + $ttl) . ' GMT';
            $attrs[] = 'Expires=' . $expires;
            $attrs[] = 'Max-Age=' . (string)$ttl;
        }

        $cookie = sprintf('%s=%s; %s', $this->keyName, $value, implode('; ', $attrs));

        $response->addHeader('Set-Cookie', $cookie);
    }

    public function clearInResponse(OutboundResponse $response): void
    {
        $cookie = sprintf(
            '%s=deleted; Path=/; Expires=Thu, 01 Jan 1970 00:00:00 GMT; Path=/',
            $this->keyName
        );

        $response->addHeader('Set-Cookie', $cookie);
    }    
}
/** End of CookieSessionChannel **/