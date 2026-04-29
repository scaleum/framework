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

namespace Scaleum\Stdlib\Helpers;

use Scaleum\Stdlib\Exceptions\EInvalidArgumentException;
use Scaleum\Stdlib\Exceptions\ERuntimeError;

/**
 * EnvHelper
 *
 * @author Maxim Kirichenko <kirichenko.maxim@gmail.com>
 */
class EnvHelper
{
    protected const INTERPOLATE_DEFAULT_OPTIONS = [
        'strict'          => true,
        'allowEmpty'      => true,
        'preserveUnknown' => false,
        'variables'       => null,
    ];

    public static function has(string $key): bool
    {
        return array_key_exists($key, $_ENV) || getenv($key) !== false;
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        if (array_key_exists($key, $_ENV)) {
            return $_ENV[$key];
        }

        $value = getenv($key);
        return $value === false ? $default : $value;
    }

    public static function set(string $key, mixed $value): void
    {
        $value = (string) $value;
        putenv(sprintf('%s=%s', $key, $value));
        $_ENV[$key] = $value;
    }

    public static function interpolateArray(array $items, array $options = []): array
    {
        $options = self::resolveInterpolateOptions($options);
        $result  = [];
        foreach ($items as $key => $value) {
            if (is_array($value)) {
                $result[$key] = self::interpolateArray($value, $options);
            } elseif (is_string($value)) {
                $result[$key] = self::interpolateString($value, $options);
            } else {
                $result[$key] = $value;
            }
        }

        return $result;
    }

    public static function interpolateString(string $value, array $options = []): string
    {
        $options = self::resolveInterpolateOptions($options);

        if (! str_contains($value, '${')) {
            return $value;
        }

        return preg_replace_callback('/\$\{([A-Za-z_][A-Za-z0-9_]*)(?:(:-|:\?)([^}]*))?\}/', function (array $match) use ($options): string {
            $var      = $match[1];
            $operator = $match[2] ?? null;
            $operand  = $match[3] ?? '';
            $raw      = $match[0];

            $found      = false;
            $envValue   = self::interpolateGetValue($var, $found, $options);
            $allowEmpty = (bool) $options['allowEmpty'];

            if ($found && ($allowEmpty || $envValue !== '')) {
                return $envValue;
            }

            if ($operator === ':-') {
                return $operand;
            }

            if ($operator === ':?') {
                $message = $operand !== ''
                    ? $operand
                    : sprintf('Environment variable `%s` is required', $var);

                throw new ERuntimeError($message);
            }

            if ((bool) $options['preserveUnknown']) {
                return $raw;
            }

            if ((bool) $options['strict']) {
                throw new ERuntimeError(sprintf('Environment variable `%s` is not set', $var));
            }

            return '';
        }, $value) ?? throw new EInvalidArgumentException('Failed to interpolate environment placeholders');
    }

    protected static function interpolateGetValue(string $key, bool &$found, array $options): string
    {
        $variables = $options['variables'];
        if (is_array($variables)) {
            if (array_key_exists($key, $variables)) {
                $found = true;
                return (string) $variables[$key];
            }
            $found = false;
            return '';
        }

        if (self::has($key)) {
            $found = true;
            return (string) self::get($key, '');
        }

        $found = false;
        return '';
    }

    protected static function resolveInterpolateOptions(array $options): array
    {
        return array_replace(self::INTERPOLATE_DEFAULT_OPTIONS, $options);
    }
}
/** End of EnvHelper **/
