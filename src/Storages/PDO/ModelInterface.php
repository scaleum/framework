<?php
declare(strict_types=1);
/**
 * This file is part of Scaleum Framework.
 *
 * (C) 2009-2025 Maxim Kirichenko <kirichenko.maxim@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Scaleum\Storages\PDO;

interface ModelInterface {
    public function find(mixed $id): ?self;
    public function findOneBy(array $conditions): ?self;
    public function findAll(): array;
    public function findAllBy(array $conditions): array;
    public function load(array $input): self;
    public function insert(): int;
    public function update(): int;
    public function delete(bool $cascade = false): int;
    public function isExisting(): bool;
    public function getId(): mixed;
    public function getMode(): ?string;
    public function setMode(string $mode): self;
    public function getTable(): string;
    public function getPrimaryKey(): string;
    public function getData();
    public function getLastStatus(): array;
}