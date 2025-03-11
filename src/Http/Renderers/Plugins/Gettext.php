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

namespace Scaleum\Http\Renderers\Plugins;

use Scaleum\Http\Renderers\TemplateRenderer;
use Scaleum\i18n\Translator;
use Scaleum\Services\ServiceLocator;

/**
 * Gettext
 *
 * @author Maxim Kirichenko <kirichenko.maxim@gmail.com>
 */
class Gettext implements RendererPluginInterface {
    protected $renderer;

    public function getName(): string {
        return 'gettext';
    }

    public function register(TemplateRenderer $renderer): void {
        $this->renderer = $renderer;
    }

    public function __invoke(string $message, string $textDomain = 'default', ?string $locale = NULL) {
        if (($instance = ServiceLocator::get('translator')) instanceof Translator) {
            return $instance->translate($message, $textDomain, $locale);
        }
        return $message;
    }
}
/** End of Gettext **/