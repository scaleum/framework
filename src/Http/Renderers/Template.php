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

/**
 * Template
 *
 * @author Maxim Kirichenko <kirichenko.maxim@gmail.com>
 */
class Template {
    protected string $content   = '';
    protected ?string $filename = null;
    protected array $data       = [];
    protected bool $partial     = false;

    public function __construct(string $view, array $data = [], bool $partial = false) {
        if (self::isTemplateText($view) || self::isTemplateFile($view) === false) {
            $this->content = $view;
        } else {
            $this->filename = $view;
        }
        $this->data    = $data;
        $this->partial = $partial;
    }

    public static function isTemplateFile(string $view): bool {
        if (! is_string($view) || ! mb_check_encoding($view, 'UTF-8')) {
            return false;
        }

        return preg_match('/^[\w\-\/:.]+$/u', $view) === 1;
    }

    public static function isTemplateText(string $view): bool {
        return preg_match('/[\s{}<>]/u', $view) === 1;
    }

    public function __toString() {
        return (string) $this->content;
    }

    /**
     * Get the value of content
     */
    public function getContent(): string {
        return $this->content;
    }

    /**
     * Set the value of content
     *
     * @return  self
     */
    public function setContent(string $content) {
        $this->content = $content;

        return $this;
    }

    /**
     * Get the value of filename
     */
    public function getFilename(): ?string {
        return $this->filename;
    }

    /**
     * Set the value of filename
     *
     * @return  self
     */
    public function setFilename(string $filename) {
        $this->filename = $filename;

        return $this;
    }

    /**
     * Get the value of data
     */
    public function getData(): array {
        return $this->data;
    }

    /**
     * Set the value of data
     *
     * @return  self
     */
    public function setData(array $data) {
        $this->data = $data;

        return $this;
    }

    /**
     * Get the value of partial
     */
    public function getPartial(): bool {
        return $this->partial;
    }

    /**
     * Set the value of partial
     *
     * @return  self
     */
    public function setPartial(bool $partial) {
        $this->partial = $partial;

        return $this;
    }
}
/** End of Template **/