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

namespace Scaleum\Http;

use Psr\Http\Message\StreamInterface;

/**
 * MessagePayload
 *
 * @author Maxim Kirichenko <kirichenko.maxim@gmail.com>
 */
final class MessagePayload {
    public function __construct(
        private array $headers,
        private StreamInterface $stream
    ) {}

    public function getHeaders(): array {
        return $this->headers;
    }

    public function getBodyStream(): StreamInterface {
        return $this->stream;
    }
}
/** End of MessagePayload **/