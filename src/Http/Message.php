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

use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\StreamInterface;

/**
 * Message
 *
 * @author Maxim Kirichenko <kirichenko.maxim@gmail.com>
 */
class Message implements MessageInterface {
    protected array $headers = [];
    protected StreamInterface $body;
    protected string $protocol;

    public function __construct(array $headers = [], ?StreamInterface $body = null, string $protocol = '1.1') {
        $this->headers  = $headers;
        $this->body     = $body ?? new Stream(fopen('php://temp', 'r+'));
        $this->protocol = $protocol;
    }

    public function getProtocolVersion(): string {
        return $this->protocol;
    }

    public function withProtocolVersion($version): static
    {
        $clone           = clone $this;
        $clone->protocol = $version;
        return $clone;
    }

    public function getHeaders(): array {
        return $this->headers;
    }

    public function hasHeader($name): bool {
        return isset($this->headers[$name]);
    }

    public function getHeader($name): array {
        return $this->headers[$name] ?? [];
    }

    public function getHeaderLine($name): string {
        return implode(', ', $this->getHeader($name));
    }

    public function withHeader($name, $value): static
    {
        $clone                 = clone $this;
        $clone->headers[$name] = is_array($value) ? $value : [$value];
        return $clone;
    }

    public function withAddedHeader($name, $value): static
    {
        $clone                 = clone $this;
        $clone->headers[$name] = array_merge($this->headers[$name] ?? [], (array) $value);
        return $clone;
    }

    public function withoutHeader($name): static
    {
        $clone = clone $this;
        unset($clone->headers[$name]);
        return $clone;
    }

    public function getBody(): StreamInterface {
        return $this->body;
    }

    public function withBody(StreamInterface $body): static
    {
        $clone       = clone $this;
        $clone->body = $body;
        return $clone;
    }
}
/** End of Message **/