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

use Scaleum\Console\Contracts\ConsoleResponseInterface;
use Scaleum\Http\Stream;

/**
 * Response
 *
 * @author Maxim Kirichenko <kirichenko.maxim@gmail.com>
 */
class Response implements ConsoleResponseInterface {
    private ?string $content = null;
    private int $statusCode = ConsoleResponseInterface::STATUS_SUCCESS;
    public function setContent(string $content): void {
        $this->content = $content;
    }

    public function getContent(): ?string {
        return $this->content;
    }

    public function getStatusCode(): int {
        return $this->statusCode;
    }

    public function setStatusCode(int $statusCode): void {
        if (! in_array($statusCode, ConsoleResponseInterface::STATUSES)) {
            throw new \InvalidArgumentException('Invalid status code');
        }
        $this->statusCode = $statusCode;
    }

    public function send(): void {
        if($this->content) {
            $stream = $this->statusCode !== ConsoleResponseInterface::STATUS_SUCCESS ? STDERR : STDOUT;
            fwrite($stream, $this->content . PHP_EOL);
        }
    }
}
/** End of Response **/