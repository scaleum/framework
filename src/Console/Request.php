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

namespace Scaleum\Console;

use Scaleum\Console\Contracts\ConsoleRequestInterface;

/**
 * ConsoleRequest
 *
 * @author Maxim Kirichenko <kirichenko.maxim@gmail.com>
 */
class Request implements ConsoleRequestInterface {
    private $args;

    public function __construct() {
        // Сохраняем все аргументы, кроме имени файла (первого)
        $this->args = array_slice($_SERVER['argv'], 1);
    }

    // Получить необработанные аргументы
    public function getRawArguments(): array {
        return $this->args;
    }
}

/** End of ConsoleRequest **/