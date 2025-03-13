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

namespace Scaleum\Http;

use Psr\Http\Message\ResponseInterface;
use Scaleum\Stdlib\Helpers\HttpHelper;
/**
 * ClientResponse
 *
 * @author Maxim Kirichenko <kirichenko.maxim@gmail.com>
 */
class ClientResponse extends Message implements ResponseInterface
{
    protected int $statusCode;
    public function __construct(
        int $statusCode = 200,
        array $headers = [],
        mixed $body = null,
        string $protocol = '1.1'
    ) {
        parent::__construct($headers, $body, $protocol);
        $this->statusCode = $statusCode;
    }


    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    public function withStatus($code, $reasonPhrase = ''): static
    {
        $clone = clone $this;
        $clone->statusCode = $code;
        return $clone;
    }

    public function getReasonPhrase(): string
    {
        return HttpHelper::getStatusMessage($this->statusCode);
    }
}
/** End of ClientResponse **/