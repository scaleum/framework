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

namespace Scaleum\Stdlib\Exception;

use Scaleum\Stdlib\Base\AutoInitialized;

/**
 * ExceptionOutputAbstarct
 *
 * @author Maxim Kirichenko <kirichenko.maxim@gmail.com>
 */
abstract class ExceptionOutputAbstarct extends AutoInitialized implements ExceptionRendererInterface {
    /**
     * Base path (for overlap).
     * $base_path property, which can be represents the base path of the project's files.
     * This property is NULL and can be set to a string value representing the base path
     * that will be excluded from filenames when rendering(backtrace).
     *
     * @var string|null $base_path The base path for rendering.
     */
    protected ?string $base_path = null;

    /**
     * Determines whether traces should be included in the rendered output.
     *
     * @var bool $include_traces
     */
    protected bool $include_traces = true;
    /**
     * Determines whether the full namespace is allowed.
     *
     * @var bool $allow_fullnamespace
     */
    protected bool $allow_fullnamespace = false;
}
/** End of ExceptionOutputAbstarct **/