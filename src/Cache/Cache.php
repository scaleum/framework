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

use Scaleum\Cache\CacheInterface;
use Scaleum\Cache\Drivers\FilesystemDriver;
use Scaleum\Cache\Drivers\NullDriver;
use Scaleum\Stdlib\Base\Hydrator;

class Cache extends Hydrator implements CacheInterface {
    protected ?CacheInterface $driver = null;
    protected bool $enabled                 = false;

    /**
     * Cleans the cache.
     *
     * This method is responsible for cleaning the cache and removing any expired or unnecessary data.
     *
     * @return bool Returns `true` if the cache was successfully cleaned, `false` otherwise.
     */
    public function clean(): bool {
        if ($this->isEnabled() && ($driver = $this->getDriver())) {
            return $driver->clean();
        }
        return false;
    }

    /**
     * Checks if a cache entry exists for the given ID.
     *
     * @param string $id The ID of the cache entry.
     * @return bool Returns true if the cache entry exists, false otherwise.
     */
    public function has(string $id): bool {
        if ($this->isEnabled() && ($driver = $this->getDriver())) {
            return $driver->has($id);
        }
        return false;
    }

    /**
     * Deletes a cache entry by its ID.
     *
     * @param string $id The ID of the cache entry to delete.
     * @return bool True if the cache entry was successfully deleted, false otherwise.
     */
    public function delete(string $id): bool {
        if ($this->isEnabled() && ($driver = $this->getDriver())) {
            return $driver->delete($id);
        }

        return false;
    }

    /**
     * Retrieves the value associated with the given ID from the cache.
     *
     * @param string $id The ID of the value to retrieve.
     * @return mixed The value associated with the given ID, or null if it doesn't exist in the cache.
     */
    public function get(string $id): mixed {
        if ($this->isEnabled() && ($driver = $this->getDriver())) {
            return $driver->get($id);
        }

        return null;
    }

    /**
     * Retrieves the metadata associated with the given cache ID.
     *
     * @param string $id The cache ID.
     * @return mixed The metadata associated with the cache ID.
     */
    public function getMetadata(string $id): mixed {
        if ($this->isEnabled() && ($driver = $this->getDriver())) {
            return $driver->getMetadata($id);
        }

        return null;
    }

    /**
     * Checks if the cache is enabled.
     *
     * @return bool Returns true if the cache is enabled, false otherwise.
     */
    public function isEnabled(): bool {
        return $this->enabled === true;
    }

    /**
     * Saves data to the cache.
     *
     * @param string $id The identifier for the data.
     * @param mixed $data The data to be saved.
     * @return bool Returns true if the data was successfully saved, false otherwise.
     */
    public function save(string $id, mixed $data): bool {
        if ($this->isEnabled() && ($driver = $this->getDriver())) {
            return $driver->save($id, $data);
        }

        return false;
    }

    /**
     * Returns the enabled status of the cache.
     *
     * @return bool The enabled status of the cache.
     */
    public function getEnabled(): bool {
        return $this->enabled;
    }

    /**
     * Sets the enabled status of the cache.
     *
     * @param bool $enabled The enabled status of the cache.
     * @return self Returns the current instance of the Cache class.
     */
    public function setEnabled(bool $enabled): self {
        $this->enabled = $enabled;
        return $this;
    }

    /**
     * Get the value of driver
     */
    public function getDriver(): CacheInterface {
        if ($this->driver === null) {
            $this->driver = $this->getDriverDefault();
        }

        return $this->driver;
    }

    /**
     * Set the value of driver
     *
     * @return  self
     */
    public function setDriver(mixed $driver) {
        if (! is_object($driver)) {
            $driver = static::createInstance($driver);
        }

        if (! ($driver instanceof CacheInterface)) {
            throw new \InvalidArgumentException('Invalid driver');
        }

        $this->driver = $driver;

        return $this;
    }

    /**
     * Retrieves the default driver for the cache.
     *
     * @return CacheInterface The default driver for the cache.
     */
    protected function getDriverDefault(): CacheInterface {
        return new NullDriver();
    }
}

/* End of file Cache.php */
