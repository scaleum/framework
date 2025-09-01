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
        if ($context === null || ! is_object($context)) {
            $context = $this;
        }

        if (count($config)) {
            foreach ($config as $key => $val) {
                if (is_numeric($key)) {
                    continue;
                }

                $normalizedKey = StringHelper::normalizeName($key);
                $method        = "set$normalizedKey";
                if (method_exists($context, $method)) {
                    call_user_func([$context, $method], $val);
                } else {
                    if (property_exists($context, $key)) {
                        $reflection = new \ReflectionObject($context);
                        if ($reflection->hasProperty($key)) {
                            $property = $reflection->getProperty($key);
                            if ($property->isPublic() || $property->isProtected()) {
                                $context->{$key} = $val;
                            } elseif ($property->isPrivate()) {
                                $property->setAccessible(true);
                                $property->setValue($context, $val);
                            }
                        }
                    }
                }
            }
        }
    }
}
