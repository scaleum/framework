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

namespace Scaleum\Cache;

interface CacheInterface {
    /**
     * Cleans the cache.
     *
     * @return bool True if the cache was successfully cleaned, false otherwise.
     */
    public function clean(): bool;

    /**
     * Checks if an item with the given ID exists in the storage.
     *
     * @param string $id The ID of the item to check.
     * @return bool True if the item exists, false otherwise.
     */
    public function has(string $id): bool;
    /**
     * Deletes an item from the cache.
     *
     * @param string $id The ID of the item to delete.
     * @return bool True if the item was successfully deleted, false otherwise.
     */
    public function delete(string $id): bool;

    /**
     * Retrieves the data associated with the given ID.
     *
     * @param string $id The ID of the data to retrieve.
     * @return mixed The retrieved data.
     */
    public function get(string $id): mixed;

    /**
     * Retrieves the metadata associated with the given ID.
     *
     * @param string $id The ID of the data.
     * @return mixed The metadata associated with the ID.
     */
    public function getMetadata(string $id): mixed;

    /**
     * Saves data with the given ID.
     *
     * @param string $id The ID of the data.
     * @param mixed $data The data to be saved.
     * @return bool Returns true if the data was successfully saved, false otherwise.
     */
    public function save(string $id, mixed $data): bool;

}