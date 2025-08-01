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
use Scaleum\Stdlib\Helpers\HttpHelper;

// Example:
//  [
//      'path'     => '/api/user(?:/({:any}))?',
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

    protected ?string $path    = null;
    private ?string $pathRaw   = null;
    protected mixed $methods   = null;
    protected ?array $callback = null;

    public function getMethods(): array {
        if ($this->methods === null) {
            return HttpHelper::ALLOWED_HTTP_METHODS;
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
        $this->pathRaw = $path;
        $this->path    = $this->decode($path);
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
        $template = $this->pathRaw ?? $this->path ?? '';
        $url      = $template;

        // Remove leading and trailing slashes
        $url = preg_replace_callback('#\(\?:/(\({[^}]+}\))\)\?#', function ($m) use (&$params) {
            // If there is no value for the nested placeholder, remove the entire group
            $key = null;
            if (preg_match('#{:(\w+)}#', $m[1], $km)) {
                $key = $km[1];
            }

            if (is_numeric(array_key_first($params))) {
                return isset($params[0]) ? "/$params[0]" : '';
            } elseif ($key && isset($params[$key])) {
                return '/' . $params[$key];
            }

            return '';
        }, $url);

        // Replace remaining placeholders
        $url = preg_replace_callback(self::REGVAL, function ($m) use (&$params) {
            $token = trim($m[0], '{}');
            if (str_starts_with($token, ':')) {
                $value = is_numeric(array_key_first($params)) ? array_shift($params) : '';
                return $value;
            }
            return '';
        }, $url);

        // Build query string from named parameters
        $query = http_build_query(array_filter($params, 'is_string', ARRAY_FILTER_USE_KEY));
        if ($query) {
            $url .= "?$query";
        }

        return $url;
    }

    /**
     * Decodes the given path.
     *
     * @param string $path The path to decode.
     * @return string The decoded path.
     */
    private function decode($path) {
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