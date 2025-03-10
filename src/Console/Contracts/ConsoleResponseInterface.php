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

namespace Scaleum\Console\Contracts;

use Scaleum\Core\Contracts\ResponderInterface;


/**
 * ResponseInterface
 *
 * @author Maxim Kirichenko <kirichenko.maxim@gmail.com>
 */
interface ConsoleResponseInterface extends ResponderInterface
{
    public const STATUS_SUCCESS = 0;
    public const STATUS_NOT_FOUND = 1;
    public const STATUS_INVALID_PARAMS = 2;

    public const STATUSES = [
        self::STATUS_SUCCESS,
        self::STATUS_NOT_FOUND,
        self::STATUS_INVALID_PARAMS
    ];

    public function setContent(string $content): void;
    public function getContent(): ?string;
    public function getStatusCode(): int;
    public function setStatusCode(int $statusCode): void;
}
/** End of ResponseInterface **/