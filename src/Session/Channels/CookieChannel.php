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

use Scaleum\Http\CookieManager;
use Scaleum\Http\InboundRequest;
use Scaleum\Http\OutboundResponse;
use Scaleum\Session\Contracts\SessionChannelInterface;

/**
 * CookieSessionChannel
 *
 * @author Maxim Kirichenko <kirichenko.maxim@gmail.com>
 */
class CookieChannel extends CookieManager implements SessionChannelInterface
{
    protected string $keyName = 'SID';
    protected string $salt    = '771dc153d1d74684b252ecde98a9b6f1';

    public function fetchFromRequest(InboundRequest $request): ?string
    {
        $cookies = $request->getCookieParams();
        if (! empty($cookies[$this->keyName])) {
            return $this->restore(rawurldecode((string) $cookies[$this->keyName]));
        }

        return null;
    }

    public function writeToResponse(OutboundResponse $response, string $id, ?int $ttl = null): void
    {
        $expires = $ttl !== null && $ttl > 0 ? time() + $ttl : null;
        $maxAge  = $ttl !== null && $ttl > 0 ? $ttl : null;

        $this->setToResponse($response, $this->keyName, $id, $expires, $maxAge);
    }

    public function clearInResponse(OutboundResponse $response): void
    {
        $this->deleteFromResponse($response, $this->keyName, 'deleted');
    }

    public function getSecret(): string
    {
        return $this->getSalt();
    }

    public function setSecret(string $secret): static
    {
        $this->setSalt($secret);
        return $this;
    }

    public function setSameSite(string $sameSite): static
    {
        parent::setSameSite($sameSite);
        if ($sameSite === 'None' && ! $this->isSecure()) {
            $this->setSecure(true);
        }

        return $this;
    }
}
/** End of CookieSessionChannel **/
