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

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UriInterface;

/**
 * ClientRequest
 *
 * @author Maxim Kirichenko <kirichenko.maxim@gmail.com>
 */
class ClientRequest extends Message implements RequestInterface {
    protected string $method;
    protected UriInterface $uri;
    protected ?string $requestTarget = null;

    public function __construct(
        string $method,
        UriInterface $uri,
        array $headers = [],
        StreamInterface $body = null,
        string $protocol = '1.1'
    ) {
        parent::__construct($headers, $body, $protocol);
        $this->method = strtoupper($method);
        $this->uri    = $uri;
    }

    public function getRequestTarget(): string {
        return $this->requestTarget ?: (string) $this->uri;
    }

    public function withRequestTarget($requestTarget): static
    {
        $clone      = clone $this;
        $clone->uri = $clone->uri->withPath($requestTarget);
        return $clone;
    }

    public function getMethod(): string {
        return $this->method;
    }

    public function withMethod($method): static
    {
        $clone         = clone $this;
        $clone->method = strtoupper($method);
        return $clone;
    }

    public function getUri(): UriInterface {
        return $this->uri;
    }

    public function withUri(UriInterface $uri, $preserveHost = false): static
    {
        $clone      = clone $this;
        $clone->uri = $uri;

        if (! $preserveHost || ! $this->hasHeader('Host')) {
            $clone = $clone->withHeader('Host', $uri->getHost());
        }

        return $clone;
    }
}
/** End of ClientRequest **/