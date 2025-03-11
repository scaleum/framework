<?php
/**
 * @author    Maxim Kirichenko
 * @copyright Copyright (c) 2009-2017 Maxim Kirichenko (kirichenko.maxim@gmail.com)
 * @license   GNU General Public License v3.0 or later
 */

namespace Scaleum\i18n;

use Avant\Http\Application;


/**
 * Trait TranslatorTrait
 * @subpackage Avant\i18n
 */
trait TranslatorTrait
{
    /**
     * @return null|Translator
     */
    public function getTranslatorInstance()
    {
        if (($instance = Application::getInstance()->getComponent( 'translator' )) instanceof Translator) {
            return $instance;
        }

        return null;
    }

    public function translate($message, $textDomain = 'default', $locale = null)
    {
        if ($translator = $this->getTranslatorInstance()) {
            return $translator->translate( $message, $textDomain, $locale );
        }

        return $message;
    }
}