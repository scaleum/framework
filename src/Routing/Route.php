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

namespace Scaleum\Routing;

use Scaleum\Stdlib\Base\Hydrator;

// Example:
//  [
//      'path'     => '/api/user(?:/({:any}))?',
//      'name'     => 'service',
//      'methods'  => 'GET|POST',
//      'callback' => [
//          'controller' => 'Application\Controllers\Service',
//          or
//          'controller' => [
//              'class' => 'Application\Controllers\Service',
//              'args'  => [
//                  'arg_1 => 'value_1',
//                  'arg_2 => 'value_2',
//              ],
//          ],

//          'method'     => 'index',
//          or
//          'method'     => 'index{:0...n}',
//      ],
//  ]

/**
 * Route
 *
 * @author Maxim Kirichenko <kirichenko.maxim@gmail.com>
 */
class Route extends Hydrator implements RouteInterface {
    protected const REGVAL     = '/({.+?})/';
    protected const REGVAL_REV = '/(\(.+?\))/';
    protected const WILDCARDS  = [
        ':any'  => '.*',
        ':num'  => '[0-9]+',
        ':slug' => '[a-z\-]+',
        ':text' => '[a-zA-Z]+',
    ];
    public const HTTP_GET     = 'GET';
    public const HTTP_POST    = 'POST';
    public const HTTP_PUT     = 'PUT';
    public const HTTP_PATCH   = 'PATCH';
    public const HTTP_DELETE  = 'DELETE';
    public const HTTP_OPTIONS = 'OPTIONS';
    public const HTTP_HEAD    = 'HEAD';

    public const ALLOWED_HTTP_METHODS = [
        self::HTTP_GET,
        self::HTTP_POST,
        self::HTTP_PUT,
        self::HTTP_PATCH,
        self::HTTP_DELETE,
        self::HTTP_OPTIONS,
        self::HTTP_HEAD,
    ];

    protected ?string $path    = null;
    protected ?string $name    = null;
    protected mixed $methods   = null;
    protected ?array $callback = null;

    public function getMethods(): array {
        if ($this->methods === null) {
            return self::ALLOWED_HTTP_METHODS;
        }
        return $this->methods;
    }

    /**
     * Set the HTTP methods for the route.
     *
     * @param string|array $methods The HTTP methods to set. This can be a string or an array of strings - 'GET|POST',['GET','POST'[,'PUT','DELETE']].
     * @return self Returns the current instance for method chaining.
     */
    public function setMethods(string | array $methods): self {
        if (is_string($methods)) {
            $methods = explode('|', strtoupper($methods));
        }

        $this->methods = $methods;
        return $this;
    }


    /**
     * Get the path of the route.
     *
     * @return string The path of the route.
     */
    public function getPath(): string {
        if (empty($this->path)) {
            throw new \RuntimeException('Route `path` is not defined or empty');
        }
        return $this->path;
    }


    /**
     * Sets the path for the route.
     *
     * @param string $path The path to set for the route.
     * @return self Returns the instance of the route for method chaining.
     */
    public function setPath(string $path): self {
        $this->path = $this->decode($path);
        return $this;
    }


    /**
     * Get the name of the route.
     *
     * @return string The name of the route.
     */
    public function getName(): string {
        if (empty($this->name)) {
            throw new \RuntimeException('Route `name` is not defined or empty');
        }
        return $this->name;
    }

    /**
     * Set the name of the route.
     *
     * @param string $name The name to set for the route.
     * @return self Returns the instance of the route for method chaining.
     */
    public function setName(string $name): self {
        $this->name = $name;
        return $this;
    }

    /**
     * Get the callback for the route.
     *
     * @return array The callback for the route.
     */
    public function getCallback(): array {
        return $this->callback;
    }


    /**
     * Set the callback for the route.
     *
     * @param array $callback The callback to set for the route.
     * @return self Returns the instance of the route for method chaining.
     */
    public function setCallback(array $callback): self {
        $this->callback = $callback;
        return $this;
    }

    /**
     * Generates a URL based on the provided parameters.
     *
     * @param array $params An indexed array of parameters to be included in the URL.
     * @return string The generated URL as a string.
     */
    public function getUrl(array $params): string {
        $url = $this->encode($this->getPath());
        if ($params && preg_match_all(self::REGVAL_REV, $url, $matches)) {
            $matches = array_slice($matches[1], 0, count($params));
            $total   = count($matches);
            for ($i = 0; $i < $total; $i++) {
                $url = str_replace($matches[$i], $params[$i] ?? '', $url);
            }
        }

        return $url;
    }

    /**
     * Encodes the given path.
     *
     * @param string $path The path to encode.
     * @return string The encoded path.
     */
    protected function encode($path) {
        $result = preg_replace_callback(static::REGVAL_REV, function ($matches) {
            $patterns   = array_flip(static::WILDCARDS);
            $matches[0] = str_replace(['(', ')', '/^', '$/'], '', $matches[0]);
            if (in_array($matches[0], array_keys($patterns))) {
                return '({' . $patterns[$matches[0]] . '})';
            } else {
                return "($matches[0])";
            }

        }, $path
        );

        return str_replace('\/', '/', trim($result, '/^$'));
    }

    /**
     * Decodes the given path.
     *
     * @param string $path The path to decode.
     * @return string The decoded path.
     */
    protected function decode($path) {
        $result = preg_replace_callback(static::REGVAL, function ($matches) {
            $patterns   = static::WILDCARDS;
            $matches[0] = str_replace(['{', '}'], '', $matches[0]);
            if (in_array($matches[0], array_keys($patterns))) {
                return $patterns[$matches[0]];
            }

            return null;
        }, $path
        );

        return '/^' . str_replace('/', '\/', $result) . '$/';
    }
}
/** End of Route **/