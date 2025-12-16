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

namespace Scaleum\Session\Contracts;

use Scaleum\Http\InboundRequest;
use Scaleum\Http\OutboundResponse;

/**
 * SessionChannelInterface
 *
 * @author Maxim Kirichenko <kirichenko.maxim@gmail.com>
 */
interface SessionChannelInterface
{
    /**
     * Забрать идентификатор сессии из входящего запроса.
     */
    public function fetchFromRequest(InboundRequest $request): ?string;

    /**
     * Записать идентификатор в исходящий ответ
     * (Set-Cookie, заголовок и т.п.).
     */
    public function writeToResponse(OutboundResponse $response, string $id, ?int $ttl = null): void;

    /**
     * Очистить идентификатор у клиента (logout).
     */
    public function clearInResponse(OutboundResponse $response): void;
}
/** End of SessionChannelInterface **/