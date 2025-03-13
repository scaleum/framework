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

use Scaleum\Stdlib\Exceptions\ERuntimeError;

class LockHandle {
    public mixed $fileHandle;
    public string $processName;
    public string $lockFile;

    public function __construct(mixed $fileHandle, string $processName, string $lockFile) {
        if (! is_resource($fileHandle)) {
            throw new ERuntimeError(sprintf('Invalid file handle: %s, given %s', $lockFile, gettype($fileHandle)));
        }

        $this->fileHandle  = $fileHandle;
        $this->processName = $processName;
        $this->lockFile    = $lockFile;
    }
}