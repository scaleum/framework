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

/**
 * CommnadAbstract
 *
 * @author Maxim Kirichenko <kirichenko.maxim@gmail.com>
 */
abstract class CommnadAbstract implements CommandInterface {
    protected ?ConsoleOptions $options = null;

    /**
     * Get the value of options
     */
    public function getOptions(): ConsoleOptions {
        if (! $this->options instanceof ConsoleOptions) {
            $this->options = new ConsoleOptions();
        }

        return $this->options;
    }

    public function printLine(string $message, bool $isError = false): void {
        $stream = $isError ? STDERR : STDOUT;
        fwrite($stream, $message . PHP_EOL);
    }
}
/** End of CommnadAbstract **/