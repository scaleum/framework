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

namespace Scaleum\Http\Client\Transport;

use Scaleum\Stdlib\Base\Hydrator;

/**
 * TransportAbstract
 *
 * @author Maxim Kirichenko <kirichenko.maxim@gmail.com>
 */
abstract class TransportAbstract extends Hydrator implements TransportInterface {

    protected int $redirectsCount = 5;
    protected int $timeout        = 5;

    protected static function flatten(array $array): array
    {
        $result = [];
        foreach ($array as $key => $value) {
            $result[] = sprintf('%s: %s', $key, $value);
        }

        return $result;
    }


    /**
     * Get the value of redirectsCount
     */
    public function getRedirectsCount(): int {
        return $this->redirectsCount;
    }

    /**
     * Set the value of redirectsCount
     *
     * @return  self
     */
    public function setRedirectsCount(int $redirectsCount): static
    {
        $this->redirectsCount = $redirectsCount;

        return $this;
    }

    /**
     * Get the value of timeout
     */
    public function getTimeout(): int {
        return $this->timeout;
    }

    /**
     * Set the value of timeout
     *
     * @return  self
     */
    public function setTimeout(int $timeout): static
    {
        $this->timeout = $timeout;

        return $this;
    }
}
/** End of TransportAbstract **/