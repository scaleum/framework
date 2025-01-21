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

/**
 * EventManagerInterface
 *
 * @author Maxim Kirichenko <kirichenko.maxim@gmail.com>
 */
interface EventManagerInterface {
    public function on(string | Event | array $event, mixed $callback = null, int $priority = 1): array | Listener;
    public function dispatch(string | Event $event, mixed $context = null, array $params = [], mixed $callback = null): array;
}
/** End of EventManagerInterface **/