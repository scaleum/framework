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

use Scaleum\Console\Contracts\CommandInterface;
use Scaleum\Console\Contracts\ConsoleRequestInterface;
use Scaleum\Console\Contracts\ConsoleResponseInterface;

/**
 * CommandDispatcher
 *
 * @author Maxim Kirichenko <kirichenko.maxim@gmail.com>
 */
class CommandDispatcher {
    private $commands = [];

    public function registerCommand(string $name, CommandInterface $command): void {
        $this->commands[$name] = $command;
    }

    public function dispatch(ConsoleRequestInterface $request): ConsoleResponseInterface {
        $commandName = $request->getRawArguments()[0] ?? null;

        if ($commandName && isset($this->commands[$commandName])) {
            $response = $this->commands[$commandName]->execute($request);
        } else {
            $response = new Response();
            $response->setContent("Error: Command " . ($commandName ? "'{$commandName}' not found." : "is empty."));
            $response->setStatusCode(ConsoleResponseInterface::STATUS_NOT_FOUND);
        }

        return $response;
    }
}
/** End of CommandDispatcher **/