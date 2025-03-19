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
use Psr\Http\Message\ResponseInterface;
use Scaleum\Core\Contracts\ResponderInterface;
use Scaleum\Stdlib\Helpers\HttpHelper;

/**
 * Response
 *
 * @author Maxim Kirichenko <kirichenko.maxim@gmail.com>
 */
class ServerResponse extends Message implements ResponseInterface, ResponderInterface {
    use StreamTrait;
    protected int $statusCode;
    public function __construct(
        int $statusCode = 200,
        array $headers = [],
        mixed $body = null,
        string $protocol = '1.1'
    ) {
        $this->body       = $this->createStream($body);
        $this->headers    = $headers;
        $this->statusCode = $statusCode;

        parent::__construct($this->headers, $this->body, $protocol);
    }

    public function getStatusCode(): int {
        return $this->statusCode;
    }

    public function withStatus($code, $reasonPhrase = ''): static
    {
        $clone             = clone $this;
        $clone->statusCode = $code;
        return $clone;
    }

    public function getReasonPhrase(): string {
        return HttpHelper::getStatusMessage($this->statusCode);
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