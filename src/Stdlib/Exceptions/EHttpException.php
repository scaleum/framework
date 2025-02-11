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

namespace Scaleum\Stdlib\Exceptions;

use Scaleum\Stdlib\Helpers\HttpHelper;

/**
 * EHttpException
 *
 * @author Maxim Kirichenko <kirichenko.maxim@gmail.com>
 */
class EHttpException extends EBaseException
{
    public function __construct(int $code = 500, string $message = '', \Throwable $previous = null)
    {
        # Overriding the code if it is not a valid HTTP status code
        if(HttpHelper::isStatusCode($code) === false) {
            $code = 500;
        }
 
        if($message === '') {
            $message = HttpHelper::getStatusMessage($code);
        }

        parent::__construct($message, $code, previous:$previous);
    }
}
/** End of EHttpException **/