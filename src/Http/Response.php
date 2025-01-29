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

namespace Scaleum\Http;

use Scaleum\Core\ResponseInterface;

/**
 * Response
 *
 * @author Maxim Kirichenko <kirichenko.maxim@gmail.com>
 */
class Response implements ResponseInterface
{
    public function send(?string $content = null): void{
        if(null !== $content){
            echo $content;
        }
    }
}
/** End of Response **/