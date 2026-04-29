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
use Scaleum\Stdlib\Helpers\EnvHelper;
use Scaleum\Stdlib\Exceptions\ENotFoundError;
use Scaleum\Stdlib\Exceptions\ETypeException;

/**
 * Config
 *
 * @author Maxim Kirichenko <kirichenko.maxim@gmail.com>
 */
class Config extends Registry {
    protected ?LoaderResolver $resolver = null;

    public function __construct(array $items = [], string $separator = '.', ?LoaderResolver $resolver = null) {
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
     * Resolves placeholders in all string config values.
     *
     * Supported placeholders:
     * - ${VAR}
     * - ${VAR:-default}
     * - ${VAR:?message}
     */
    public function resolvePlaceholders(array $options = []): self {
        $this->setItems(EnvHelper::interpolateArray($this->getItems(), $options));
        return $this;
    }

    public function getString(string $key, ?string $default = null): string {
        $value = $this->resolveValue($key, $default, func_num_args() > 1);
        if (! is_string($value)) {
            throw new ETypeException(sprintf(
                '%s: key `%s` must be string, `%s` provided',
                __METHOD__,
                $key,
                get_debug_type($value)
            ));
        }

        return $value;
    }

    public function getInt(string $key, ?int $default = null): int {
        $value = $this->resolveValue($key, $default, func_num_args() > 1);
        if (! is_int($value)) {
            throw new ETypeException(sprintf(
                '%s: key `%s` must be int, `%s` provided',
                __METHOD__,
                $key,
                get_debug_type($value)
            ));
        }

        return $value;
    }

    public function getFloat(string $key, ?float $default = null): float {
        $value = $this->resolveValue($key, $default, func_num_args() > 1);
        if (! is_float($value)) {
            throw new ETypeException(sprintf(
                '%s: key `%s` must be float, `%s` provided',
                __METHOD__,
                $key,
                get_debug_type($value)
            ));
        }

        return $value;
    }

    public function getBool(string $key, ?bool $default = null): bool {
        $value = $this->resolveValue($key, $default, func_num_args() > 1);
        if (! is_bool($value)) {
            throw new ETypeException(sprintf(
                '%s: key `%s` must be bool, `%s` provided',
                __METHOD__,
                $key,
                get_debug_type($value)
            ));
        }

        return $value;
    }

    public function getArray(string $key, ?array $default = null): array {
        $value = $this->resolveValue($key, $default, func_num_args() > 1);
        if (! is_array($value)) {
            throw new ETypeException(sprintf(
                '%s: key `%s` must be array, `%s` provided',
                __METHOD__,
                $key,
                get_debug_type($value)
            ));
        }

        return $value;
    }

    private function resolveValue(string $key, mixed $default, bool $hasDefault): mixed {
        if (! $this->has($key)) {
            if ($hasDefault) {
                return $default;
            }

            throw new ENotFoundError(sprintf('%s: key `%s` not found', __METHOD__, $key));
        }

        return $this->get($key);
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