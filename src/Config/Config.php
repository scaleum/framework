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

namespace Scaleum\Config;

use Scaleum\Stdlib\Base\Registry;

/**
 * Config
 *
 * @author Maxim Kirichenko <kirichenko.maxim@gmail.com>
 */
class Config extends Registry {
    protected ?LoaderResolver $resolver = null;

    public function __construct(array $items = [], string $separator = '/', ?LoaderResolver $resolver = null) {
        parent::__construct($items, $separator);
        if ($resolver !== null) {
            $this->setResolver($resolver);
        }
    }

    public function fromFile(string $filename, ?string $key = null): self {
        $this->merge($this->getResolver()->fromFile($filename), $key);
        return $this;
    }

    public function fromFiles(array $files, ?string $key = null): self {
        $array = [];
        foreach ($files as $file) {
            $array = array_replace_recursive($array, $this->getResolver()->fromFile($file));
        }
        $this->merge($array, $key);
        return $this;
    }

    /**
     * Get the value of resolver
     */
    public function getResolver() {
        if ($this->resolver === null) {
            $this->resolver = new LoaderResolver();
        }
        return $this->resolver;
    }

    /**
     * Set the value of resolver
     *
     * @return  self
     */
    public function setResolver(LoaderResolver $resolver): self {
        $this->resolver = $resolver;
        return $this;
    }
}
/** End of Config **/