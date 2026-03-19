<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Scaleum\Stdlib\Helpers\EnvHelper;

final class EnvHelperTest extends TestCase
{
    private array $backupEnv = [];

    protected function setUp(): void
    {
        parent::setUp();
        $this->backupEnv = $_ENV;
    }

    protected function tearDown(): void
    {
        foreach (array_keys($_ENV) as $key) {
            if (! array_key_exists($key, $this->backupEnv)) {
                putenv($key);
                unset($_ENV[$key]);
            }
        }

        foreach ($this->backupEnv as $key => $value) {
            putenv(sprintf('%s=%s', $key, (string) $value));
            $_ENV[$key] = $value;
        }

        parent::tearDown();
    }

    public function testGetReturnsDefaultWhenMissing(): void
    {
        putenv('SCALEUM_TEST_MISSING');
        unset($_ENV['SCALEUM_TEST_MISSING']);

        self::assertSame('fallback', EnvHelper::get('SCALEUM_TEST_MISSING', 'fallback'));
        self::assertFalse(EnvHelper::has('SCALEUM_TEST_MISSING'));
    }

    public function testSetAndGetRoundtrip(): void
    {
        EnvHelper::set('SCALEUM_TEST_KEY', 'value');

        self::assertTrue(EnvHelper::has('SCALEUM_TEST_KEY'));
        self::assertSame('value', EnvHelper::get('SCALEUM_TEST_KEY'));
    }

    public function testGetPreservesEmptyStringAndZeroString(): void
    {
        EnvHelper::set('SCALEUM_TEST_EMPTY', '');
        EnvHelper::set('SCALEUM_TEST_ZERO', '0');

        self::assertSame('', EnvHelper::get('SCALEUM_TEST_EMPTY', 'fallback'));
        self::assertSame('0', EnvHelper::get('SCALEUM_TEST_ZERO', 'fallback'));
    }
}
