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
use Psr\Http\Message\UriInterface;
use Scaleum\Stdlib\Helpers\StringHelper;

/**
 * Uri
 *
 * @author Maxim Kirichenko <kirichenko.maxim@gmail.com>
 */
class Uri implements UriInterface {
    private string $scheme;
    private string $userInfo;
    private string $host;
    private ?int $port;
    private string $path;
    private string $query;
    private string $fragment;

    public function __construct(?string $uri = null) {
        if ($uri === null) {
            $uri = $this->fetchUriString();
        }

        $uri    = StringHelper::cleanInvisibleChars('/' . ltrim($uri ?? '/', '/'), false);
        $parsed = parse_url($uri);

        $this->scheme   = $parsed['scheme'] ?? '';
        $this->userInfo = isset($parsed['user'])
        ? ($parsed['user'] . (isset($parsed['pass']) ? ':' . $parsed['pass'] : ''))
        : '';
        $this->host     = $parsed['host'] ?? '';
        $this->port     = $parsed['port'] ?? null;
        $this->path     = $parsed['path'] ?? '';
        $this->query    = $parsed['query'] ?? '';
        $this->fragment = $parsed['fragment'] ?? '';
    }

    public function getScheme(): string {
        return $this->scheme;
    }

    public function withScheme($scheme): static
    {
        $clone         = clone $this;
        $clone->scheme = $scheme;
        return $clone;
    }

    public function getAuthority(): string {
        $authority = $this->host;
        if ($this->userInfo !== '') {
            $authority = "{$this->userInfo}@$authority";
        }
        if ($this->port !== null) {
            $authority .= ":{$this->port}";
        }
        return $authority;
    }

    public function getUserInfo(): string {
        return $this->userInfo;
    }

    public function withUserInfo($user, $password = null): static
    {
        $clone           = clone $this;
        $clone->userInfo = $password ? "$user:$password" : $user;
        return $clone;
    }

    public function getHost(): string {
        return $this->host;
    }

    public function withHost($host): static
    {
        $clone       = clone $this;
        $clone->host = $host;
        return $clone;
    }

    public function getPort(): ?int {
        return $this->port;
    }

    public function withPort($port): static
    {
        $clone       = clone $this;
        $clone->port = $port;
        return $clone;
    }

    public function getPath(): string {
        return $this->path;
    }

    public function withPath($path): static
    {
        $clone       = clone $this;
        $clone->path = $path;
        return $clone;
    }

    public function getQuery(): string {
        return $this->query;
    }

    public function withQuery($query): static
    {
        $clone        = clone $this;
        $clone->query = $query;
        return $clone;
    }

    public function getFragment(): string {
        return $this->fragment;
    }

    public function withFragment($fragment): static
    {
        $clone           = clone $this;
        $clone->fragment = $fragment;
        return $clone;
    }

    public function __toString(): string {
        $uri = '';

        if ($this->scheme !== '') {
            $uri .= "{$this->scheme}://";
        }

        if ($authority = $this->getAuthority()) {
            $uri .= $authority;
        }

        if ($this->path !== '') {
            if ($uri && $this->path[0] !== '/') {
                $uri .= '/';
            }
            $uri .= $this->path;
        }

        if ($this->query !== '') {
            $uri .= "?{$this->query}";
        }

        if ($this->fragment !== '') {
            $uri .= "#{$this->fragment}";
        }

        return $uri;
    }

    private function fetchUriString(): ?string {
        // Let's try the REQUEST_URI first, this will work in most situations
        if ($uri = $this->fetchUri()) {
            return $uri;
        }

        // Is there a PATH_INFO variable?
        // Note: some servers seem to have trouble with getenv() so we'll test it two ways
        $path = $_SERVER['PATH_INFO'] ?? @getenv('PATH_INFO');
        if (is_string($path) && trim($path, '/') != '') {
            return $path;
        }

        // No PATH_INFO?... What about QUERY_STRING?
        $path = $_SERVER['QUERY_STRING'] ?? @getenv('QUERY_STRING');
        if (is_string($path) && trim($path, '/') != '') {
            return $path;
        }

        // As a last ditch effort lets try using the $_GET array
        if (is_array($_GET) && count($_GET) == 1 && ($key = trim(key($_GET), '/')) != '') {
            return $key;
        }

        // We've exhausted all our options...
        return null;
    }

    private function fetchUri(): ?string {
        if (! isset($_SERVER['REQUEST_URI']) or ! isset($_SERVER['SCRIPT_NAME'])) {
            return null;
        }

        $uri = $_SERVER['REQUEST_URI'];
        if (strpos($uri, $_SERVER['SCRIPT_NAME']) === 0) {
            $uri = substr($uri, strlen($_SERVER['SCRIPT_NAME']));
        } elseif (strpos($uri, dirname($_SERVER['SCRIPT_NAME'])) === 0) {
            $uri = substr($uri, strlen(dirname($_SERVER['SCRIPT_NAME'])));
        }

        // This section ensures that even on servers that require the URI to be in the query string (Nginx) a correct
        // URI is found, and also fixes the QUERY_STRING server var and $_GET array.
        if (strncmp($uri, '?/', 2) === 0) {
            $uri = substr($uri, 2);
        }
        $parts = preg_split('#\?#i', $uri, 2);
        $uri   = $parts[0];
        if (isset($parts[1])) {
            $_SERVER['QUERY_STRING'] = $parts[1];
            parse_str($_SERVER['QUERY_STRING'], $_GET);
        } else {
            $_SERVER['QUERY_STRING'] = '';
            $_GET                    = [];
        }

        if ($uri == '/' || empty($uri)) {
            return '/';
        }

        $uri = parse_url($uri, PHP_URL_PATH);

        // Do some final cleaning of the URI and return it
        return str_replace(['//', '../'], '/', trim($uri, '/'));
    }
}
/** End of Uri **/