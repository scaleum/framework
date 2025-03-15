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
use Scaleum\Core\Contracts\ResponderInterface;

/**
 * Response
 *
 * @author Maxim Kirichenko <kirichenko.maxim@gmail.com>
 */
class ServerResponse extends ClientResponse implements ResponderInterface {
    use StreamTrait;

    public function __construct(
        int $statusCode = 200,
        array $headers = [],
        mixed $body = null,
        string $protocol = '1.1'
    ) {
        $this->headers = $headers;
        $this->body    = $this->createStream($body);

        parent::__construct($statusCode, $this->headers, $this->body, $protocol);
    }

    // Отправка HTTP-ответа клиенту
    public function send(): void {
        header(sprintf('HTTP/%s %d %s', $this->protocol, $this->statusCode, $this->getReasonPhrase()), true, $this->statusCode);
        foreach ($this->headers as $name => $values) {
            foreach ($values as $value) {
                header(sprintf('%s: %s', $name, $value), false);
            }
        }

        fpassthru($this->body->detach());
    }
}
/** End of ServerResponse **/