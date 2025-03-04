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

/**
 * Benchmark
 *
 * @author Maxim Kirichenko <kirichenko.maxim@gmail.com>
 */
class Benchmark extends Hydrator {
    public const SUFFIX_START = '_start';
    public const SUFFIX_STOP  = '_stop';
    protected array $markers  = [];

    public function elapsed(string $name, int $decimals = 4) {

        $pointStart = $this->prepPoint($name, self::SUFFIX_START);
        $pointEnd   = $this->prepPoint($name, self::SUFFIX_STOP);

        if (! isset($this->markers[$pointStart])) {
            return '';
        }

        if (! isset($this->markers[$pointEnd])) {
            $this->markers[$pointEnd] = @microtime(true);
        }

        return number_format($this->markers[$pointEnd] - $this->markers[$pointStart], $decimals);
    }

    public function getMarkers() {
        $suffix     = self::SUFFIX_START;
        $suffix_len = strlen($suffix);

        return array_map(function ($point) use ($suffix_len) {
            return substr($point, 0, strlen($point) - $suffix_len);
        }, array_keys(array_filter($this->markers, function ($key) use ($suffix) {
            return strpos($key, $suffix) !== FALSE;
        }, ARRAY_FILTER_USE_KEY)));
    }

    public function start(array | string $point, $timer = null): static
    {
        if (is_array($point)) {
            foreach ($point as $pointItem) {
                $this->start($pointItem);
            }

            return $this;
        }

        $this->setPoint($this->prepPoint($point, self::SUFFIX_START), $timer);

        return $this;
    }

    public function stop(array | string $point, ?float $timer = null): static
    {
        if (is_array($point)) {
            foreach ($point as $pointItem) {
                $this->stop($pointItem);
            }

            return $this;
        }

        $this->setPoint($this->prepPoint($point, self::SUFFIX_STOP), $timer);

        return $this;
    }

    protected function prepPoint(string $name, $suffix = self::SUFFIX_START): string {
        $name = strtolower($name);
        $name = str_replace(['\\', '/', '//', ' '], '_', $name);

        if ($suffix != substr($name, -1, strlen($suffix))) {
            $name .= $suffix;
        }

        return $name;
    }

    protected function setPoint(string $name, ?float $time = null) {
        $this->markers[$name] = $time ?? @microtime(true);
    }
}
/** End of Benchmark **/