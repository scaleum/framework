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
use Scaleum\Stdlib\Helpers\FileHelper;
use Scaleum\Stdlib\Helpers\PathHelper;

/**
 * IncludeAsset
 *
 * @author Maxim Kirichenko <kirichenko.maxim@gmail.com>
 */
class IncludeAsset implements RendererPluginInterface {
    protected $renderer;

    public function getName(): string {
        return 'asset';
    }

    public function register(TemplateRenderer $renderer): void {
        $this->renderer = $renderer;
    }

    public function __invoke(string $path) {
        $filename = FileHelper::prepFilename(PathHelper::join($_SERVER['DOCUMENT_ROOT'] ?? '/', $path), true);
        $version  = file_exists($filename) ? filemtime($filename) : time();
        return "{$path}?v={$version}";
    }
}
/** End of IncludeAsset **/
