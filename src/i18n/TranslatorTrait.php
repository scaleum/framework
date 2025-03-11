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

use Scaleum\Services\ServiceLocator;

trait TranslatorTrait {
    public function getTranslatorInstance(): ?Translator {
        if (($instance = ServiceLocator::get('translator')) instanceof Translator) {
            return $instance;
        }

        return null;
    }

    public function translate($message, $textDomain = 'default', $locale = null): string {
        if ($translator = $this->getTranslatorInstance()) {
            return $translator->translate($message, $textDomain, $locale);
        }

        return $message;
    }
}