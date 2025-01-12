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
namespace Scaleum\Stdlib\Helpers;

class JsonHelper
{
    const DEFAULT_JSON_FLAGS = JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRESERVE_ZERO_FRACTION | JSON_INVALID_UTF8_SUBSTITUTE | JSON_PARTIAL_OUTPUT_ON_ERROR;

    /**
     * Checks if a string is a valid JSON.
     *
     * @param mixed $string The string to check.
     * @return bool Returns true if the string is a valid JSON, false otherwise.
     */
    public static function isJson(mixed $string)
    {
        return is_string($string) && is_array(json_decode($string, true)) && (json_last_error() == JSON_ERROR_NONE) ? true : false;
    }

    /**
     * Encodes the given data into a JSON string.
     *
     * @param mixed $data The data to be encoded.
     * @param int|null $encode_flags Optional. Bitmask consisting of JSON constants to control the encoding process.
     * @return string The JSON string representation of the data.
     */
    public static function encode(mixed $data, ?int $encode_flags = null): string
    {
        if ($encode_flags === null) {
            $encode_flags = self::DEFAULT_JSON_FLAGS;
        }

        $json = json_encode($data, $encode_flags);
        if ($json === false) {
            self::throwEncodeError(json_last_error(), $data);
        }

        return $json;
    }

    /**
     * Throws an encode error with the specified code and data.
     *
     * @param int $code The error code.
     * @param mixed $data The data causing the error.
     * @return never This function never returns a value.
     */
    private static function throwEncodeError(int $code, mixed $data): never
    {
        $message = match ($code) {
            JSON_ERROR_DEPTH => 'The maximum stack depth has been exceeded',
            JSON_ERROR_STATE_MISMATCH => 'Occurs with underflow or with the modes mismatch',
            JSON_ERROR_CTRL_CHAR => 'Control character error, possibly incorrectly encoded',
            JSON_ERROR_SYNTAX => 'Syntax error',
            JSON_ERROR_UTF8 => 'Malformed UTF-8 characters, possibly incorrectly encoded',
            JSON_ERROR_RECURSION => 'Object or array include recursive references and cannot be encoded',
            JSON_ERROR_INF_OR_NAN => 'A value includes either NAN or INF',
            JSON_ERROR_UNSUPPORTED_TYPE => 'A value of an unsupported type was given',
            default => 'Unknown error',
        };

        throw new \RuntimeException("JSON encoding failed: $message");
    }
}

/* End of file JsonHelper.php */
