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

use Scaleum\Storages\Redis\Client;

/**
 * RedisSession
 *
 * @author Maxim Kirichenko <kirichenko.maxim@gmail.com>
 */
class RedisSession extends SessionAbstract {
    protected int $database     = 0;
    protected string $host      = '127.0.0.1';
    protected int $port         = 6379;
    protected string $namespace = 'session';
    private ?Client $resource   = null;

    protected function read(): array {
        $result = [];
        foreach ($this->getRedisResource()->keys($this->getKey()) as $key) {
            $parts = explode(':', $key);
            $var   = end($parts);

            $result[$var] = json_decode($this->getRedisResource()->get($key), TRUE);
        }

        return $result;
    }

    protected function write(array $data): void {
        foreach ($data as $key => $value) {
            $this->getRedisResource()->set($this->getKey($key), json_encode($value, JSON_FORCE_OBJECT), $this->getExpiration());
        }        
    }

    protected function delete(): void {
        // do nothing
        // all resources will be deleted automatically after expiration time
        
        // FIXME - implement delete method
    }

    public function cleanup(): void {
        // do nothing
        // all resources will be deleted automatically after expiration time
    }

    /**
     * Return key(s) for current session
     * @param string $key
     * @return string
     */
    protected function getKey($key = '*') {
        return sprintf("%s:%s:%s", $this->getNamespace(), $this->id, $key);
    }

    protected function getRedisResource() {
        if (! $this->resource instanceof Client) {
            $this->resource = new Client(['host' => $this->getHost(),'port' => $this->getPort(), 'lifetime' => $this->getExpiration(), 'db' => $this->getDatabase()]);
        }

        return $this->resource;
    }

    /**
     * Get the value of host
     */
    public function getHost() {
        return $this->host;
    }

    /**
     * Set the value of host
     *
     * @return  self
     */
    public function setHost(string $host) {
        $this->host = $host;

        return $this;
    }
    /**
     * Get the value of namespace
     */
    public function getNamespace() {
        return $this->namespace;
    }

    /**
     * Set the value of namespace
     *
     * @return  self
     */
    public function setNamespace(string $namespace) {
        $this->namespace = $namespace;

        return $this;
    }

    /**
     * Get the value of port
     */
    public function getPort() {
        return $this->port;
    }

    /**
     * Set the value of port
     *
     * @return  self
     */
    public function setPort(int $port) {
        $this->port = $port;
        return $this;
    }

    /**
     * Get the value of database
     */
    public function getDatabase(): int {
        return $this->database;
    }

    /**
     * Set the value of database
     *
     * @return  self
     */
    public function setDatabase(int $database) {
        $this->database = $database;
        return $this;
    }
}
/** End of RedisSession **/