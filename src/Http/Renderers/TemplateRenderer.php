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

namespace Scaleum\Http\Renderers;

use Scaleum\Http\Renderers\Plugins\Gettext;
use Scaleum\Http\Renderers\Plugins\IncludeAsset;
use Scaleum\Http\Renderers\Plugins\IncludeTemplate;
use Scaleum\Http\Renderers\Plugins\RendererPluginInterface;
use Scaleum\Stdlib\Base\Hydrator;
use Scaleum\Stdlib\Exceptions\EInOutException;
use Scaleum\Stdlib\Exceptions\ENotFoundError;
use Scaleum\Stdlib\Exceptions\ERuntimeError;
use Scaleum\Stdlib\Helpers\ArrayHelper;
use Scaleum\Stdlib\Helpers\FileHelper;
use Scaleum\Stdlib\Helpers\PathHelper;

/**
 * TemplateRenderer
 *
 * @author Maxim Kirichenko <kirichenko.maxim@gmail.com>
 */
class TemplateRenderer extends Hydrator {
    protected array $locations = [];
    protected array $views     = [];
    protected array $plugins   = [];
    protected string $layout   = 'layout';

    public function __construct(array $config = []) {
        parent::__construct($config);
        /**
         * Лимит обратных ссылок PCRE
         * При малых значениях могут не обрабатываться шаблоны целиком
         * По-умолчанию: 1000000 (но бывают исключения)
         */
        ini_set('pcre.backtrack_limit', '2000000');

        /**
         * Лимит на рекурсию. Не забывайте о том, что если вы установите достаточно высокое значение,
         * то PCRE может превысить размер стэка (установленный операционной системой) и в конце концов вызовет крушение PHP
         * По-умолчанию: 100000
         */
        ini_set('pcre.recursion_limit', '500000');

        $this->setPlugins([
            IncludeTemplate::class,
            IncludeAsset::class,
            Gettext::class,
        ]);
    }

    public function __call($method, $args) {
        if ($this->hasPlugin($method)) {
            $plugin = $this->getPlugin($method);
            if (is_callable($plugin)) {
                return call_user_func_array($plugin, array_values($args));
            }
        }
        return null;
    }

    public function __get($name) {
        if ($this->hasPlugin($name)) {
            return $this->getPlugin($name);
        }

        return null;
    }

    protected function resolveTemplate(string $name): string {
        $filename = $name;

        // has view?
        if ($this->hasView($name)) {
            $filename = $this->getView($name);
        }

        // is file?
        if (! file_exists($filename)) {
            $filename = FileHelper::prepFilename($filename, false);
            foreach ($this->locations as $location) {
                if (is_file($path = PathHelper::join($location, $filename))) {
                    $this->setView($name, $path);
                    return $path;
                }
            }
        } else {
            return $filename;
        }

        throw new ENotFoundError(sprintf('Template "%s" is not found', $name));
    }

    /**
     * Get the value of locations
     */
    public function getLocations() {
        return $this->locations;
    }

    /**
     * Set the value of locations
     *
     * @return  self
     */
    public function setLocations(array $locations, bool $primary = true) {
        foreach ($locations as $location) {
            $this->addLocation($location, $primary);
        }

        return $this;
    }

    public function addLocation(string $location, bool $primary = true) {
        $location = FileHelper::prepPath($location);
        if (($location != '/') && ! in_array($location, $this->locations)) {
            if ($primary) {
                array_unshift($this->locations, $location);
            } else {
                array_push($this->locations, $location);
            }
        }

        return $this;
    }

    /**
     * Get the value of views
     */
    public function getViews(): array {
        return $this->views;
    }

    /**
     * Set the value of views
     *
     * @return  self
     */
    public function setViews(array $views) {
        foreach ($views as $alias => $filename) {
            $this->setView($alias, $filename);
        }

        return $this;
    }

    public function hasView(string $name): bool {
        return isset($this->views[$name]);
    }

    public function setView(string $name, string $filename) {
        $this->views[$name] = $filename;
        return $this;
    }

    public function getView(string $name): string | null {
        return $this->views[$name] ?? null;
    }

    public function registerPlugin(RendererPluginInterface $plugin): void {
        $this->plugins[$plugin->getName()] = $plugin;
        $plugin->register($this);
    }

    public function getPlugins(): array {
        return $this->plugins;
    }

    public function getPlugin(string $name): RendererPluginInterface {
        if (isset($this->plugins[$name])) {
            return $this->plugins[$name];
        }

        throw new ENotFoundError(sprintf('Plugin "%s" is not found', $name));
    }

    public function hasPlugin(string $name): bool {
        return isset($this->plugins[$name]);
    }

    public function setPlugins(array $plugins): self {
        foreach ($plugins as $plugin) {
            $instance = $plugin;
            if (is_string($plugin) || is_array($plugin)) {
                $instance = static::createInstance($plugin);
            }

            if (! is_object($instance)) {
                throw new ERuntimeError(sprintf('Plugin must be an object, given "%s"', gettype($instance)));
            }

            if (! $instance instanceof RendererPluginInterface) {
                throw new ERuntimeError(sprintf('Plugin "%s" must implement `RendererPluginInterface`', get_class($instance)));
            }

            $this->registerPlugin($instance);
        }

        return $this;
    }
    protected function parsePlugins(string $str): string {
        // isolation of {{}} syntax in <input> and <textarea>
        $patterns = [
            '~<textarea.*?>(.*?)</textarea>~isu',
            '~<input.*?(?:value\s*=\s*"(.*?)"\s*).*?/?>~isu',
        ];
        foreach ($patterns as $pattern) {
            preg_match_all($pattern, $str, $matches);
            for ($i = 0, $cnt = count($matches[0]); $i < $cnt; $i++) {
                if (! empty($matches[1][$i])) {
                    $safe = str_replace(['{{', '}}'], ['<insul>', '</insul>'], $matches[1][$i]);
                    $str  = str_replace($matches[0][$i], str_replace($matches[1][$i], $safe, $matches[0][$i]), $str);
                }
            }
        }

        // matching {{pluginName:arg1 arg2 arg3}} and {{pluginName:arg1 arg2 arg3}}content{/pluginName} tags
        preg_match_all(
            '~{{(' . implode('|', array_keys($this->plugins)) . ')(?:[\: ]([^}{]+))?}}(?:([\x00-\xFF]*?){{/\\1}})?~i',
            $str,
            $matches
        );

        // handle unique tags, keeping corresponding indexes
        $tags = array_unique($matches[0]);
        foreach ($tags as $tag) {
            // corresponding index in the original array of matches
            $key     = array_search($tag, $matches[0], true);
            $name    = $matches[1][$key];
            $args    = trim($matches[2][$key] ?? '');
            $content = trim($matches[3][$key] ?? '');

            if (! $this->hasPlugin($name)) {
                continue;
            }

            $plugin = $this->getPlugin($name);
            if (! is_callable($plugin)) {
                continue;
            }

            // parse named parameters: --var=val, --var val, --var "val with spaces"
            // simple mask: '~--?(?<key>[^=\s]+)[=\s](?:"(?<quoted>[^"]*)"|(?<simple>[^\s"]+))~x' does not support escaped characters inside quotes (\\.) and does not preserve a readable group structure
            $params = $args;
            $parsed = [];
            if (preg_match_all(
                '~--?(?<key>[^=\s]+)[=\s]+(?:"(?<quoted>(?:\\\\.|[^"\\\\])*)"|(?<simple>[^\s"]+))~x',
                $args,
                $mkv
            )) {
                foreach ($mkv['key'] as $i => $keyName) {
                    $parsed[$keyName] = $mkv['quoted'][$i] !== '' ? $mkv['quoted'][$i] : $mkv['simple'][$i];
                }
                $params = $parsed;
            }
            // if pipe-separated like: var1|var2
            elseif (is_string($args) && strpos($args, '|') !== false) {
                $params = explode('|', $args);
            }
            // one string parameter
            if (is_string($params) && empty($parsed)) {
                $params = [$params];
            }

            // add `content` as last argument(?)
            if ($content !== '') {
                if (! is_array($params)) {
                    $params = [$content];
                } else {
                    $params[] = $content;
                }
            }

            // call plugin: named parameters through reflection
            if (is_array($params) && ArrayHelper::isAssociative($params)) {
                $refMethod = new \ReflectionMethod($plugin, '__invoke');
                $args      = [];
                foreach ($refMethod->getParameters() as $paramMeta) {
                    $pName = $paramMeta->getName();
                    if (array_key_exists($pName, $params)) {
                        $args[] = $params[$pName];
                    } elseif ($paramMeta->isDefaultValueAvailable()) {
                        $args[] = $paramMeta->getDefaultValue();
                    } else {
                        throw new ERuntimeError(
                            sprintf('Missing parameter "%s" for plugin "%s"', $pName, $name)
                        );
                    }
                }
                $replacement = $refMethod->invokeArgs($plugin, $args);
            } else {
                // positional parameters are passed as an array, so we need to convert them to a list of arguments
                $replacement = call_user_func_array($plugin, array_values((array) $params));
            }

            // substitution of result
            if (is_scalar($replacement) || (is_object($replacement) && method_exists($replacement, '__toString'))) {
                $str = preg_replace(
                    '~' . preg_quote($tag, '~') . '~i',
                    trim((string) $replacement),
                    $str
                );
            }
        }

        // recovery of literal brackets
        return str_replace(['<insul>', '</insul>'], ['{{', '}}'], trim($str));
    }

    protected function parseStr(string $str, array $data = []) {
        foreach ($data as $key => $value) {
            if (is_string($value)) {
                $str = str_replace("{{{$key}}}", $value, $str);
            } elseif ($value instanceof Template) {
                $str = str_replace("{{{$key}}}", (string) $value, $str);
            }
        }

        return $str;
    }

    public function renderTemplate(Template $template): string {
        try {
            $raw = $template->getData();
            if (array_key_exists('this', $raw)) {
                unset($raw['this']);
            }

            // Normalize array keys
            $data = [];
            foreach ($raw as $key => $value) {
                // Acceptable: a-zA-Z_, digits
                if (preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $key)) {
                    $data[$key] = $value;
                } else {
                    // Transform: key.name → key_name
                    $normalized        = preg_replace('/[^a-zA-Z0-9_]/', '_', $key);
                    $data[$normalized] = $value;
                }
            }
            unset($raw);

            extract($data);
            $buffer = '';

            if ($template->getFilename() !== null) {
                ob_start();
                $filename      = $this->resolveTemplate($template->getFilename());
                $includeReturn = include $filename;
                $buffer        = ob_get_clean();

                if ($includeReturn === false && empty($buffer)) {
                    throw new EInOutException(sprintf('%s: Unable to render layout "%s"; file include failed', __METHOD__, $filename));
                }
            } else {
                $buffer = $template->getContent();
            }
        } catch (\Exception $exception) {
            @ob_end_clean();
            throw new ERuntimeError(sprintf('%s: %s', __METHOD__, $exception->getMessage()), $exception->getCode(), previous: $exception);
        }

        $buffer = $this->parseStr($buffer, $data);
        $buffer = $this->parsePlugins($buffer);

        $template->setContent($buffer);

        if ($template->getPartial()) {
            return $template->getContent();
        }

        $layout = new Template($this->getLayout(), ['content' => $template], true);
        return $this->renderTemplate($layout);
    }

    public function render(string $view, array $data = [], $partial = false): string {
        if ($view instanceof Template) {
            return $this->renderTemplate($view);
        } elseif (is_string($view)) {
            return $this->renderTemplate(new Template($view, $data, $partial));
        }
        throw new ERuntimeError(sprintf('View must be a string or instance of `Template`, given "%s"', gettype($view)));
    }

    public function renderPartial(string $view, array $data = []): string {
        return $this->render($view, $data, true);
    }

    /**
     * Get the value of layout
     */
    public function getLayout(): string {
        return $this->layout;
    }

    /**
     * Set the value of layout
     * Layout can be a path to layout file or alias(in $this->views)
     *
     * @return  self
     */
    public function setLayout(string $layout) {
        $this->layout = $layout;

        return $this;
    }
}
/** End of TemplateRenderer **/
