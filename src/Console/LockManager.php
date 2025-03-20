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

namespace Scaleum\Console;

use Scaleum\Stdlib\Base\Hydrator;
use Scaleum\Stdlib\Exceptions\ERuntimeError;
use Scaleum\Stdlib\Helpers\FileHelper;
use Scaleum\Stdlib\Helpers\ProcessHelper;

/**
 * LockManager
 *
 * @author Maxim Kirichenko <kirichenko.maxim@gmail.com>
 */
class LockManager extends Hydrator {
    protected ?string $lockDir = null;

    public function __construct(string $lockDir = null) {
        $this->lockDir = FileHelper::prepPath($lockDir ?? __DIR__ . '/locks/', false);

        if (! is_dir($this->lockDir)) {
            mkdir($this->lockDir, 0777, true);
        }
    }

    /**
     * Locks a process by its name.
     *
     * @param string $processName The name(some ID) of the process to lock.
     * @return mixed The result of the lock operation.
     */
    public function lock(string $processName): mixed {
        $lockFile = $this->getFilename($processName);
        // Check if the directory is writable
        if (! is_writable(dirname($lockFile))) {
            throw new ERuntimeError("Directory '{$this->lockDir}' is not writable");
            // return null;
        }

        $fp = fopen($lockFile, 'c+');
        if (! $fp) {
            throw new ERuntimeError("Failed to open file '{$lockFile}'");
            // return null;
        }

        if (flock($fp, LOCK_EX | LOCK_NB)) {
            // Check if the file is empty
            $pid = trim(stream_get_contents($fp) ?: '');
            rewind($fp);

            // If the process is active, the lock remains
            if ($pid && ProcessHelper::isStarted((int) $pid)) {
                if (! ProcessHelper::isPhpProcess((int) $pid)) {
                    // It's not our process, remove the lock
                    fclose($fp);
                    unlink($lockFile);
                } else {
                    // It's our process, keep the lock
                    fclose($fp);
                    return null;
                }
            }

            // Clear the file before writing
            ftruncate($fp, 0);
            rewind($fp);
            fwrite($fp, (string) getmypid());
            fflush($fp);

            // `flock($fp, LOCK_UN);` means that the lock is released **immediately after the write**.
            // If another process starts **before** `LockHandle` returns, a "process race" may occur.
            flock($fp, LOCK_UN);

            return new LockHandle($fp, $processName, realpath($lockFile) ?: $lockFile);
        }

        fclose($fp);
        return null; // Process is already locked
    }

    /**
     * Releases the given lock handle.
     *
     * @param LockHandle|null $lockHandle The lock handle to release, or null if no lock handle is provided.
     * @return void
     */
    public function cleanup(): void {
        foreach (glob($this->getFilename("*")) as $lockFile) {
            $fp  = fopen($lockFile, 'r');
            $pid = trim(fread($fp, filesize($lockFile) ?: 1)); // Read only 1 byte
            fclose($fp);

            // If the process is not there, remove the lock file
            if ($pid !== '' && ! ProcessHelper::isStarted((int) $pid)) {
                unlink($lockFile);
            }
        }
    }

    /**
     * Checks if the specified process is currently locked.
     *
     * @param string $processName The name of the process to check.
     * @return bool Returns true if the process is locked, false otherwise.
     */
    public function isLocked(string $processName): bool {
        $lockFile = $this->getFilename($processName);
        if (! file_exists($lockFile)) {
            return false;
        }

        $pid = trim(file_get_contents($lockFile) ?: '');
        return $pid !== '' && ProcessHelper::isStarted((int) $pid) && ProcessHelper::isPhpProcess((int) $pid);
    }

    protected function getFilename(string $basename): string {
        return "{$this->lockDir}$basename.lock";
    }
}
/** End of LockManager **/