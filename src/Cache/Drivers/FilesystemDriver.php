<?php

declare (strict_types = 1);
/**
 * This file is part of Scaleum\Cache.
 *
 * (C) 2009-2025 Maxim Kirichenko <kirichenko.maxim@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Scaleum\Cache\Drivers;

use Exception;
use RuntimeException;
use Scaleum\Cache\CacheInterface;
use Scaleum\Stdlib\Base\Hydrator;
use Scaleum\Stdlib\Helpers\FileHelper;

class FilesystemDriver extends Hydrator implements CacheInterface {
    protected int $lifetime = 60; // seconds, default 1 minute
    protected string $path;

    public function clean(): bool {
        return FileHelper::deleteFiles(FileHelper::prepPath($this->path), true);
    }

    public function delete($id): bool {
        return FileHelper::deleteFile(FileHelper::prepFilename($this->path . '/' . $this->prepID($id)));
    }

    public function has(string $id): bool {
        return file_exists(FileHelper::prepFilename($this->path . '/' . $this->prepID($id)));
    }

    public function get($id): mixed {
        if (! file_exists($file = FileHelper::prepFilename($this->path . '/' . $this->prepID($id)))) {
            return false;
        }

        $data = FileHelper::readFile($file);
        try {
            if (empty($data) || (($data = unserialize(base64_decode($data))) === false)) {
                return false;
            }
        } catch (Exception $exception) {
            return false;
        }

        if (time() > $data['time'] + $data['lifetime']) {
            $this->delete($id);
            return false;
        }

        return $data['data'];
    }

    public function getMetadata($id): mixed {
        if (! file_exists($file = FileHelper::prepFilename($this->path . '/' . $this->prepID($id)))) {
            return false;
        }

        $data = FileHelper::readFile($file);
        if (empty($data) || (($data = @unserialize(base64_decode($data))) == false)) {
            return false;
        }

        if (is_array($data)) {
            $time = filemtime($file);

            if (! isset($data['lifetime'])) {
                return false;
            }

            return [
                'expire' => $time + $data['lifetime'],
                'time'   => $time,
            ];
        }

        return false;
    }

    public function save(string $id, mixed $data): bool {
        $contents = [
            'time'     => time(),
            'lifetime' => $this->lifetime,
            'data'     => $data,
        ];

        if (FileHelper::writeFile($file = FileHelper::prepFilename($this->path . '/' . $this->prepID($id)), base64_encode(serialize($contents)))) {
            @chmod($file, 0777);
            return true;
        }

        return false;
    }

    public function setPath(string $path) {
        if (! is_dir($path)) {
            if (@mkdir($path, FileHelper::DIR_WRITE_MODE, true) == false) {
                throw new RuntimeException(sprintf('%s: failed to create dir "%s"', __METHOD__, $path));
            }
        }

        $this->path = $path;
    }

    private function prepID($id): string {
        return pathinfo($id, PATHINFO_EXTENSION) ? $id : "$id.cache";
    }

    /**
     * Get the value of lifetime
     */
    public function getLifetime() {
        return $this->lifetime;
    }

    /**
     * Set the value of lifetime
     *
     * @return  self
     */
    public function setLifetime($lifetime) {
        $this->lifetime = $lifetime;

        return $this;
    }
}

/* End of file File.php */