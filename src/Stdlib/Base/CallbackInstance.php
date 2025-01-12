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

namespace Scaleum\Stdlib\Base;

use Scaleum\Stdlib\Exception\EObjectError;

class CallbackInstance {
    protected $callback;
    protected array $params = [];

    public function __construct($callback) {
        $this->setCallback($callback);
    }

    public function getCallback() {
        return $this->callback;
    }

    public function getParams() {
        return $this->params;
    }

    public function setParams(array $params) {
        $this->params = $params;
    }

    protected function setCallback($callback): self {
        if (!is_callable($callback)) {
            // Callback is array, [class,action]
            if (is_array($callback)) {
                if (isset($callback['class']) && isset($callback['method'])) {
                    $className = $callback['class'];
                    $method    = $callback['method'];
                    $class     = new $className();

                    $this->callback = [$class, $method];
                } else {
                    throw new EObjectError(sprintf('%s: expected a "callable"; received "%s"', __METHOD__, gettype($callback)));
                }

                if (isset($callback['params'])) {
                    $this->setParams($callback['params']);
                }
            }
        } else {
            $this->callback = $callback;
        }

        return $this;
    }
}