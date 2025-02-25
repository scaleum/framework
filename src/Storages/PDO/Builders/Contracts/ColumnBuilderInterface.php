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

namespace Scaleum\Storages\PDO\Builders\Contracts;


/**
 * ColumnBuilderInterface
 *
 * @author Maxim Kirichenko <kirichenko.maxim@gmail.com>
 */
interface ColumnBuilderInterface
{
    // public function after(string $column): self;
    public function setComment(string $str): self;
    public function getComment(): ?string;
    public function setConstraint(mixed $constraint):self;
    public function getConstraint(): mixed;
    public function setDefaultValue(mixed $default, bool $quoted = true): self;
    public function getDefaultValue(): mixed;
    // public function first(): self;
    public function setColumn(string $str): self;
    public function getColumn(): ?string;
    public function setNotNull(bool $val = true): self;
    public function getNonNull(): bool;
    public function setUnique(bool $val = true): self;
    public function getUnique(): bool;
    public function setUnsigned(bool $val = true): self;
    public function getUnsigned(): bool;
    public function setTable(string $table): self;
    public function setTableMode(int $mode): self;
}
/** End of ColumnBuilderInterface **/