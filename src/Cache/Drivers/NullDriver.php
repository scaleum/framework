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

namespace Scaleum\Cache\Drivers;

use Scaleum\Cache\CacheInterface;

/**
 * NullDriver
 *
 * @author Maxim Kirichenko <kirichenko.maxim@gmail.com>
 */
class NullDriver implements CacheInterface {
    public function clean(): bool {
        return true;
    }

    public function has(string $id): bool {
        return false;
    }

    public function delete(string $id): bool {
        return true;
    }

    public function get(string $id): mixed {
        return null;
    }

    public function getMetadata(string $id): mixed {
        return null;
    }
    public function save(string $id, mixed $data): bool {
        return true;
    }
}
/** End of NullDriver **/