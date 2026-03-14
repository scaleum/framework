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
use Scaleum\i18n\LocaleDetector;

/**
 * LocaleDetectorTest
 *
 * @author Maxim Kirichenko <kirichenko.maxim@gmail.com>
 */
class LocaleDetectorTest extends TestCase {
    public function testLocale() {
        $localeDetector = new LocaleDetector();
        $this->printLine($localeDetector->getName());
        // $this->assertEquals('en_US', $localeDetector->getLocale());
    }

    protected function printLine(string $line) {
        fwrite(STDOUT, $line . "\n");
    }
}
/** End of LocaleDetectorTest **/