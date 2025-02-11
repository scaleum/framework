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

use Scaleum\Http\Renderers\Plugins\IncludeAsset;
use Scaleum\Http\Renderers\Plugins\IncludeTemplate;
use Scaleum\Http\Renderers\Plugins\RendererPluginInterface;
use Scaleum\Stdlib\Base\Hydrator;
use Scaleum\Stdlib\Exceptions\EInOutError;
use Scaleum\Stdlib\Exceptions\ENotFoundError;
use Scaleum\Stdlib\Exceptions\ERuntimeError;
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
                    $this->addView($name, $path);
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
            $this->addView($alias, $filename);
        }

        return $this;
    }

    public function hasView(string $name): bool {
        return isset($this->views[$name]);
    }

    public function addView(string $name, string $filename) {
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
            if (is_string($plugin) && class_exists($plugin)) {
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

    protected function parsePlugins($str): string {

        /**
         * изоляция синтаксиса "фильтров" для input & textarea
         * критично для использования разметки "фильтров" при редактировании (под)шаблонов через панель управления
         */
        $patterns = [
            '~<textarea.*?>(.*?)</textarea>~isu',
            '~<input.*?(?:value\s*=\s*"(.*?)"\s*).*?/?>~isu',
        ];

        foreach ($patterns as $pattern) {
            preg_match_all($pattern, $str, $matches);
            for ($i = 0; $i < count($matches[0]); $i++) {
                if (! empty($matches[1][$i])) {
                    $replace = str_replace($matches[1][$i], str_replace(['{', '}'], ['<insul>', '</insul>'], $matches[1][$i]), $matches[0][$i]);
                    $str     = str_replace($matches[0][$i], $replace, $str);
                    unset($replace);
                }
            }
        }

        preg_match_all('~{{(' . implode('|', array_keys($this->plugins)) . ')(?:[\: ]([^}{]+))?}}(?:([\x00-\xFF]*?){/\\1}})?~i', $str, $matches);
        foreach ($matchesUnique = array_unique($matches[0]) as $key => $value) {
            if ($this->hasPlugin($matches[1][$key])) {
                $plugin = $this->getPlugin($matches[1][$key]);
                if (is_callable($plugin)) {

                    // any string, below after ":"
                    $params = trim($matches[2][$key]);

                    // params may be string '-var1=val1 --var2=val2 -var3 value3 --var4="value4"', key value pair based
                    if (preg_match_all('~ --?(?<key> [^= ]+ ) [ =] (?|" (?<value> [^\\\\"]*+ (?s:\\\\.[^\\\\"]*)*+ ) "|([^ ?"]*) )~x', $params, $matches_internal) !== false) {
                        if (count($matches_internal['key'])) {
                            $params = array_combine($matches_internal['key'], $matches_internal['value']);
                        }
                    }

                    // params may be string 'param1[|param2|param3|..paramN]', value based
                    if (is_scalar($params) && strpos($params, '|') !== false) {
                        $params = explode('|', $params);
                    }

                    // params is string?
                    if (is_scalar($params)) {
                        $params = [$params];
                    }

                    // add to params any string from "content" part - {plugin}content{/plugin}
                    if (! empty($matches[3][$key])) {
                        $params[] = trim($matches[3][$key]);
                    }

                    $replacement = call_user_func_array($plugin, array_values($params));
                    if (is_scalar($replacement) || (is_object($replacement) && method_exists($replacement, '__toString'))) {
                        $pattern = "~" . preg_quote($value) . "~i";
                        $str     = preg_replace($pattern, trim($replacement), $str);
                    }
                    unset($replacement, $pattern);
                }
                unset($plugin);
            }
        }

        /**
         *  реконструкция "фильтров" для input & textarea
         */
        $str = str_replace(['<insul>', '</insul>'], ['{', '}'], $str);

        return trim($str);
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
            $data = $template->getData();
            if (array_key_exists('this', $data)) {
                unset($data['this']);
            }
            extract($data);
            $buffer = '';

            if ($template->getFilename()) {
                ob_start();
                $filename      = $this->resolveTemplate($template->getFilename());
                $includeReturn = include $filename;
                $buffer        = ob_get_clean();

                if ($includeReturn === false && empty($buffer)) {
                    throw new EInOutError(sprintf('%s: Unable to render layout "%s"; file include failed', __METHOD__, $filename));
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