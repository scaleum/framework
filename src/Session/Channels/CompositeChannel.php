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

namespace Scaleum\Session\Channels;

use Scaleum\Http\InboundRequest;
use Scaleum\Http\OutboundResponse;
use Scaleum\Session\Contracts\SessionChannelInterface;

/**
 * CompositeChannel
 *
 * @author Maxim Kirichenko <kirichenko.maxim@gmail.com>
 */
class CompositeChannel extends CookieChannel implements SessionChannelInterface {
    public function fetchFromRequest(InboundRequest $request): ?string {
        // Prefer cookie
        $result = parent::fetchFromRequest($request);
        if ($result !== null) {
            return $result;
        }

        // Fallback to header "X-<cookieName>"
        $headerName = "X-{$this->keyName}";
        $result     = $request->getHeaderLine($headerName);

        return $result !== '' ? $result : null;
    }

    public function writeToResponse(OutboundResponse $response, string $id, ?int $ttl = null): void {
        parent::writeToResponse($response, $id, $ttl);

        $headerName = "X-{$this->keyName}";
        $response->addHeader($headerName, $id);
    }

    public function clearInResponse(OutboundResponse $response): void {
        parent::clearInResponse($response);

        $headerName = "X-{$this->keyName}";
        $response->addHeader($headerName, '');
    }
}
/** End of CompositeChannel **/
