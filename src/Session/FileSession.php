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

namespace Scaleum\Session;

/**
 * FileSession
 *
 * @author Maxim Kirichenko <kirichenko.maxim@gmail.com>
 */
class FileSession extends SessionAbstract {
    protected string $path;

    public function close():static {        
        $this->cleanup();
        return parent::close();
    }

    protected function read(): array {
        $file = $this->getFilePath();
        return file_exists($file) ? unserialize(file_get_contents($file)) : [];
    }

    protected function write(array $data): void {
        if (! is_dir(dirname($this->getFilePath()))) {
            mkdir(dirname($this->getFilePath()), 0777, true);
        }

        file_put_contents($this->getFilePath(), serialize($data));
    }

    protected function delete(): void {
        $file = $this->getFilePath();
        if (file_exists($file)) {
            unlink($file);
        }
    }

    public function cleanup(): void {
        if ((rand() % 100) < 5) {
            $files = glob("{$this->path}*.json");
            foreach ($files as $file) {
                if (filemtime($file) < $this->getTimestamp() - $this->getExpiration()) {
                    unlink($file);
                }
            }
        }
    }

    private function getFilePath(): string {
        return $this->getPath() . $this->id . '.json';
    }

    /**
     * Get the value of path
     */
    public function getPath() {
        return $this->path;
    }

    /**
     * Set the value of path
     *
     * @return  self
     */
    public function setPath(string $path) {
        $this->path = rtrim($path, '/') . '/';
        return $this;
    }
}
/** End of FileSession **/