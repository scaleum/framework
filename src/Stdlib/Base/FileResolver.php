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

use Scaleum\Stdlib\Helpers\FileHelper;
use Scaleum\Stdlib\Helpers\PathHelper;

/**
 * FileResolver
 *
 * @author Maxim Kirichenko <kirichenko.maxim@gmail.com>
 */
class FileResolver implements FileResolverInterface {
    protected array $paths = [];

    public function resolve(string $filename): string | bool {
        // if $filename already exists do not check & find
        if (is_file($filename)) {
            return $filename;
        }

        $filename = FileHelper::prepFilename($filename);
        foreach ($this->getPaths() as $base_path) {
            $path = PathHelper::join($base_path, $filename);
            if (is_file($path)) {
                return $path;
            }
        }

        return false;
    }

    public function addPath(string | array $str, bool $unshift = true): self {
        if (is_array($str)) {
            foreach ($str as $item) {
                $this->addPath($item);
            }

            return $this;
        }

        $str = FileHelper::prepPath($str);
        if (! in_array($str, $this->paths)) {
            if ($unshift === true) {
                array_unshift($this->paths, $str);
            } else {
                $this->paths[] = $str;
            }
        }

        return $this;
    }

    /**
     * Get the value of paths
     */
    public function getPaths(): array {
        return $this->paths;
    }

    /**
     * Set the value of paths
     *
     * @return  self
     */
    public function setPaths(array $array): self {
        $this->paths = $array;
        return $this;
    }

    public function deletePath(string | array $str): self {
        if (is_array($str)) {
            foreach ($str as $item) {
                $this->deletePath($item);
            }

            return $this;
        }

        $str = FileHelper::prepPath($str);
        if (in_array($str, $this->paths)) {
            unset($this->paths[$str]);
        }

        return $this;
    }
}
/** End of FileResolver **/