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

namespace Scaleum\Stdlib\Exceptions;

use Scaleum\Stdlib\Base\Hydrator;

/**
 * ExceptionOutputAbstarct
 *
 * @author Maxim Kirichenko <kirichenko.maxim@gmail.com>
 */
abstract class ExceptionOutputAbstarct extends Hydrator implements ExceptionRendererInterface {
    /**
     * Base path (for overlap).
     * $basePath property, which can be represents the base path of the project's files.
     * This property is NULL and can be set to a string value representing the base path
     * that will be excluded from filenames when rendering(backtrace).
     *
     * @var string|null $basePath The base path for rendering.
     */
    protected ?string $basePath = null;

    /**
     * Determines whether traces should be included in the rendered output.
     *
     * @var bool $includeTraces
     */
    protected bool $includeTraces = true;

    /**
     * @var bool $includeDetails Indicates whether to include detailed information in the exception output.
     */
    protected bool $includeDetails = true;
    /**
     * Determines whether the full namespace is allowed.
     *
     * @var bool $allowFullnamespace
     */
    protected bool $allowFullnamespace = false;

    protected function getExceptionMessage(
        \Throwable $exception,
        ?string $fallback = 'Unknown error'
    ): string {
        $message = trim((string) $exception->getMessage());
        if ($message === '') {
            return (string) $fallback;
        }

        if (($position = stripos($message, 'Stack trace:')) !== false) {
            $message = rtrim(substr($message, 0, $position));
        }

        return $message;
    }
}
/** End of ExceptionOutputAbstarct **/