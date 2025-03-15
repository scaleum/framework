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

namespace Scaleum\Http\Client\Transport;

use Scaleum\Http\ClientRequest;
use Scaleum\Http\ClientResponse;

/**
 * TransportInterface
 *
 * @author Maxim Kirichenko <kirichenko.maxim@gmail.com>
 */
interface TransportInterface
{
    public function send(ClientRequest $request): ClientResponse;
    public function isSupported():bool;
}
/** End of TransportInterface **/