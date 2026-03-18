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

use PHPUnit\Framework\TestCase;
use Scaleum\i18n\Translator;

/**
 * TranslatorTest
 *
 * @author Maxim Kirichenko <kirichenko.maxim@gmail.com>
 */
class TranslatorTest extends TestCase {
    protected Translator $translator;
    protected function printLine(string $line) {
        fwrite(STDOUT, $line . "\n");
    }
    public function testCreateInstance(): void {
        $this->translator = new Translator();
        $this->assertInstanceOf(Translator::class, $this->translator);
    }

    public function testGetLocale(): void {
        $this->translator = new Translator();
        $this->assertNotNull($locale = $this->translator->getLocale());
        $this->printLine("Locale: $locale");
    }

    public function testGetTranslation(): void {
        $this->translator = new Translator([
            'localeBase' => __DIR__ . "/messages",
            'files'      => [
                [
                    'type'       => 'gettext',
                    'filename'   => 'application.po',
                    'textDomain' => 'default',
                ],
            ],
        ]);

        $str = $this->translator->translate('Browse');
        $this->printLine("Translate for `Browse`: $str");
        // $this->assertNotNull($messages = $this->translator->getMessages());
    }

    public function testGetTranslationForLocale(): void {
        $this->translator = new Translator([
            'localeBase' => __DIR__ . "/messages",
            'files'      => [
                [
                    'type'       => 'gettext',
                    'filename'   => 'application.po',
                    'textDomain' => 'default',
                ],
            ],
            'locale' => 'uk_UA',
        ]);

        $str = $this->translator->translate('Browse');
        $this->printLine("Translate for `Browse`: $str");
        // $this->assertNotNull($messages = $this->translator->getMessages());
    }
}
/** End of TranslatorTest **/