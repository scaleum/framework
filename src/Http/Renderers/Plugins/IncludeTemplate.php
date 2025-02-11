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

use Scaleum\Http\Renderers\Template;
use Scaleum\Http\Renderers\TemplateRenderer;

/**
 * IncludeTemplate
 *
 * @author Maxim Kirichenko <kirichenko.maxim@gmail.com>
 */
class IncludeTemplate implements RendererPluginInterface
{
    protected $renderer;

    public function getName(): string
    {
        return 'include';
    }

    public function register(TemplateRenderer $renderer): void
    {
        $this->renderer = $renderer;
    }

    public function __invoke(string $view,array $data = []) {
        return $this->renderer->renderTemplate(new Template($view, $data, true));
    }
}
/** End of IncludeTemplate **/