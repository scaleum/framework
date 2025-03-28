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

use Scaleum\Cache\CacheInterface;
use Scaleum\Stdlib\Base\Hydrator;
use Scaleum\Stdlib\Helpers\ArrayHelper;
use Scaleum\Storages\Redis\Client;

class RedisDriver extends Hydrator implements CacheInterface {
    protected int $db           = 0;
    protected string $host      = '127.0.0.1';
    protected int $lifetime     = 60;
    protected string $namespace = '';
    protected int $port         = 6379;
    protected ?Client $resource = null;

    public function clean(): bool {
        if ($redis = $this->getRedisResource()) {
            $keys = $redis->keys($this->getKey('*'));
            return (bool) $redis->del($keys);
        }
        return false;
    }
    public function has(string $id): bool {
        if ($redis = $this->getRedisResource()) {
            return $redis->exists($this->getKey($id)) > 0;
        }
        return false;
    }

    public function delete($id): bool {
        if ($redis = $this->getRedisResource()) {
            return (bool) $redis->del($this->getKey($id));
        }

        return false;
    }

    public function get(string $id): mixed {
        if ($cached = $this->getInternal($id)) {
            if (! empty($cached['data'])) {
                return unserialize(base64_decode($cached['data']));
            }
        }

        return false;
    }

    public function getMetadata(string $id): mixed {
        if ($cached = $this->getInternal($id)) {
            return [
                'expire' => ArrayHelper::element('time', $cached, 0) + ArrayHelper::element('lifetime', $cached, $this->lifetime),
                'time'   => ArrayHelper::element('time', $cached, 0),
                'data'   => ArrayHelper::element('data', $cached, null),
            ];
        }

        return false;
    }

    public function save(string $id, mixed $data): bool {
        if ($redis = $this->getRedisResource()) {
            $contents = [
                'time'     => time(),
                'lifetime' => $this->lifetime,
                'data'     => base64_encode(serialize($data)),
            ];

            if ($this->lifetime) {
                $redis->setex($this->getKey($id), $this->lifetime, json_encode($contents, JSON_FORCE_OBJECT));
            } else {
                $redis->set($this->getKey($id), json_encode($contents, JSON_FORCE_OBJECT));
            }

            return true;
        }

        return false;
    }

    protected function getInternal($id) {
        if ($redis = $this->getRedisResource()) {
            if (($value = $redis->get($this->getKey($id))) !== false) {
                return json_decode($value, true);
            }
        }

        return null;
    }

    protected function getKey($id) {
        if (! empty($this->namespace)) {
            return "{$this->namespace}:$id";
        }

        return $id;
    }

    /**
     * @return Client
     */
    protected function getRedisResource() {
        if (! $this->resource instanceof Client) {
            $this->resource = new Client(['host' => $this->getHost(),'port' => $this->getPort(), 'lifetime' => $this->getLifetime(), 'db' => $this->getDb()]);
        }

        return $this->resource;
    }

    protected function getServer() {
        return sprintf('tcp://%s:%d', $this->host, $this->port);
    }

    /**
     * Get the value of db
     */
    public function getDb() {
        return $this->db;
    }

    /**
     * Set the value of db
     *
     * @return  self
     */
    public function setDb($db) {
        $this->db = $db;

        return $this;
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
    public function setHost($host) {
        $this->host = $host;

        return $this;
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
    public function setNamespace($namespace) {
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
    public function setPort($port) {
        $this->port = $port;

        return $this;
    }
}

/* End of file Redis.php */
