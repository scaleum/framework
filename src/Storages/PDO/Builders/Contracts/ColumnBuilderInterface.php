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
    public function after(string $column): self;
    public function comment(string $str): self;
    public function defaultValue(mixed $default, bool $quoted = true): self;
    public function first(): self;
    public function name(string $str): self;
    public function notNull(bool $val = true): self;
    public function unique(bool $val = true): self;
    public function unsigned(bool $val = true): self;
    public function getColumnName(): string;
    public function setColumnName(string $name): self;
    public function getColumnPrev(): mixed;
    public function setColumnPrev(string $name): self;
}
/** End of ColumnBuilderInterface **/