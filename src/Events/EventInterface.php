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

namespace Scaleum\Events;

interface EventInterface {
    public function fireStop($flag = true): void;

    public function fireStopped():bool;

    public function getContext():mixed;

    public function getName():string;

    public function getParam(string $name, mixed $default = null): mixed;

    public function getParams():array;

    public function setContext(mixed $context): self;

    public function setName(string $name): self;

    public function setParam(string $name, mixed $value): self;

    public function setParams(array $params): self;
}