<?php

declare(strict_types=1);
/**
 * This file is part of Scaleum\Storages\Redis.
 *
 * (C) 2009-2025 Maxim Kirichenko <kirichenko.maxim@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Scaleum\Storages\Redis;

class Queue extends Client
{
    protected string $node = 'queue';

    public function deleteAll(): int
    {
        $node = $this->getNode();

        // Do the primary first, to try to avoid issues if someone uses/recreates the queue while we're nuking it.
        $this->del("$node:primary");

        // Delete other(all)
        foreach (($keys = $this->keys("$node:*")) as $key) {
            $this->del($key);
        }

        return count($keys);
    }

    public function deleteMessage($key): bool
    {
        $node = $this->getNode();
        if ($this->lrem("$node:primary", 0, $key)) {
            $this->del("$node:fetched:$key");
            $this->del("$node:value:$key");

            return true;
        }

        return false;
    }

    public function fetch($ttl = 60): mixed
    {
        $result    = null;
        $node      = $this->getNode();
        $threshold = time() - $ttl;
        $count     = $this->getMessageCount();
        while ($count--) {
            $id = $this->rpoplpush("$node:primary", "$node:primary");
            if (empty($id)) {
                break;
            }

            $now     = time();
            $fetched = $this->getset("$node:fetched:$id", (string)$now);

            if ($fetched < $threshold) {
                $result = $this->getMessage($id);
                break;
            }

            if ($fetched < $now) {
                $this->set("$node:fetched:$id", $fetched);
            }
        }

        return $result;
    }

    public function getMessage($key): mixed
    {
        $node = $this->getNode();
        if ($this->exists("$node:value:$key")) {
            return unserialize(base64_decode($this->get("$node:value:$key")));
        }
        return null;
    }

    public function getMessageCount(): int
    {
        $node = $this->getNode();

        return $this->llen("$node:primary");
    }

    public function getNode(): string
    {
        return $this->node;
    }

    public function setMessage($message, $override = false): string
    {
        $node  = $this->getNode();
        $key   = ($message instanceof QueueMessage) ? $message->getId() : QueueMessage::createId();
        $value = base64_encode(serialize($message));

        if ($this->exists("$node:value:$key")) {
            if ($override == true) {
                $this->set("$node:value:$key", $value);
                // Do not reset fetched time on override as it may lead to message being visible again for a while in the current queue
                // $this->set("$node:fetched:$key", "0");
            }

            return $key;
        }

        $this->set("$node:value:$key", $value);
        $this->set("$node:fetched:$key", "0");
        $this->lpush("$node:primary", $key);

        return $key;
    }

    public function setNode($node): self
    {
        $this->node = $node;
        return $this;
    }
}

/* End of file Queue.php */
