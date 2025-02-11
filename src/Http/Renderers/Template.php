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
    protected string $content = '';
    protected string $filename;
    protected array $data   = [];
    protected bool $partial = false;

    public function __construct(string $filename, array $data = [], bool $partial = false) {
        $this->filename = $filename;
        $this->data     = $data;
        $this->partial  = $partial;
    }

    public function __toString() {
        return (string) $this->content;
    }

    /**
     * Get the value of content
     */
    public function getContent() {
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
    public function getFilename(): string {
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