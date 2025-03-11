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

namespace Scaleum\i18n;
use Scaleum\i18n\Contracts\LocaleIdentityInterface;
use Scaleum\Stdlib\Base\Hydrator;

abstract class LocaleIdentityAbstract extends Hydrator implements LocaleIdentityInterface {
    protected ?string $name = null;
    
    public function getName(): ?string {
        if ($this->name === null && $name = $this->identify()) {
            $this->name = $name;
        }

        return $this->name;
    }

    abstract protected function identify(): bool | string;
}

/* End of file LocaleIdentityAbstract.php */
