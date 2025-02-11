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

namespace Scaleum\Http\Renderers\Plugins;

use Scaleum\Http\Renderers\TemplateRenderer;

/**
 * RendererPluginInterface
 *
 * @author Maxim Kirichenko <kirichenko.maxim@gmail.com>
 */
interface RendererPluginInterface
{
    public function getName(): string;
    public function register(TemplateRenderer $renderer): void;
}
/** End of RendererPluginInterface **/