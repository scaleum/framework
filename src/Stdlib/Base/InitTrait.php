<?php

declare(strict_types=1);
/**
 * This file is part of Scaleum\Stdlib.
 *
 * (C) 2009-2025 Maxim Kirichenko <kirichenko.maxim@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Scaleum\Stdlib\Base;

use Scaleum\Stdlib\Exceptions\EPropertyError;
use Scaleum\Stdlib\Helpers\StringHelper;

trait InitTrait
{
    /**
     * Initializes the object with the given configuration and context.
     *
     * @param array $config The configuration options.
     * @param mixed $context The context for initialization.
     * @return void
     */
    public function init(array $config = [], mixed $context = null)
    {
        if ($context == null || !is_object($context)) {
            $context = &$this;
        }

        if (count($config)) {
            foreach ($config as $key => $val) {
                if (is_numeric($key)) {
                    continue;
                }

                if (method_exists($context, $method = 'set' . StringHelper::normalizeName($key))) {
                    call_user_func([$context, $method], $val);
                } else {
                    if (!property_exists($context, $key) && (!$context instanceof \stdClass)) {
                        throw new EPropertyError(sprintf('Class  "%s" does not have the "%s" property, dynamic creation of properties is not supported', StringHelper::className($context, false), $key));
                    }

                    $context->{$key} = $val;
                }
            }
        }
    }
}
