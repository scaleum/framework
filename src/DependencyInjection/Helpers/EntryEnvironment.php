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

namespace Scaleum\DependencyInjection\Helpers;

/**
 * Environment
 *
 * It is responsible for managing the environment configurations and settings.
 * @author Maxim Kirichenko <kirichenko.maxim@gmail.com>
 */
class EntryEnvironment
{
    private string $key;
    private mixed $default;

    public function __construct(string $key, mixed $default = null)
    {
        $this->key = $key;
        $this->default = $default;
    }

    public function resolve(): mixed
    {
        return $_ENV[$this->key] ?? getenv($this->key) ?: $this->default;
    }
}