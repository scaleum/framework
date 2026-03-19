<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Scaleum\Config\Config;
use Scaleum\Stdlib\Exceptions\ENotFoundError;
use Scaleum\Stdlib\Exceptions\ERuntimeError;
use Scaleum\Stdlib\Exceptions\ETypeException;

final class ConfigTest extends TestCase
{
    public function testTypedGettersReturnExpectedTypes(): void
    {
        $config = new Config([
            'app' => [
                'name' => 'Scaleum',
                'port' => 8080,
                'ratio' => 0.5,
                'debug' => true,
                'hosts' => ['a', 'b'],
            ],
        ], '.');

        self::assertSame('Scaleum', $config->getString('app.name'));
        self::assertSame(8080, $config->getInt('app.port'));
        self::assertSame(0.5, $config->getFloat('app.ratio'));
        self::assertTrue($config->getBool('app.debug'));
        self::assertSame(['a', 'b'], $config->getArray('app.hosts'));
    }

    public function testTypedGetterThrowsWhenKeyIsMissing(): void
    {
        $config = new Config([], '.');

        $this->expectException(ENotFoundError::class);
        $config->getString('app.name');
    }

    public function testTypedGetterReturnsDefaultWhenKeyIsMissing(): void
    {
        $config = new Config([], '.');

        self::assertSame('fallback', $config->getString('app.name', 'fallback'));
        self::assertSame(42, $config->getInt('app.port', 42));
    }

    public function testTypedGetterThrowsOnTypeMismatch(): void
    {
        $config = new Config([
            'app' => ['port' => '8080'],
        ], '.');

        $this->expectException(ETypeException::class);
        $config->getInt('app.port');
    }

    public function testResolvePlaceholdersReplacesRequiredAndDefaultPlaceholders(): void
    {
        $config = new Config([
            'db' => [
                'host' => '${DB_HOST}',
                'port' => '${DB_PORT:-5432}',
                'dsn'  => 'pgsql:host=${DB_HOST};port=${DB_PORT:-5432}',
            ],
        ], '.');

        $config->resolvePlaceholders([
            'variables' => [
                'DB_HOST' => 'localhost',
            ],
        ]);

        self::assertSame('localhost', $config->getString('db.host'));
        self::assertSame('5432', $config->getString('db.port'));
        self::assertSame('pgsql:host=localhost;port=5432', $config->getString('db.dsn'));
    }

    public function testResolvePlaceholdersThrowsForMissingRequiredVariable(): void
    {
        $config = new Config([
            'db' => [
                'user' => '${DB_USER:?DB user is required}',
            ],
        ], '.');

        $this->expectException(ERuntimeError::class);
        $this->expectExceptionMessage('DB user is required');

        $config->resolvePlaceholders(['variables' => []]);
    }

    public function testResolvePlaceholdersCanPreserveUnknownPlaceholders(): void
    {
        $config = new Config([
            'db' => [
                'host' => '${DB_HOST}',
            ],
        ], '.');

        $config->resolvePlaceholders([
            'strict' => false,
            'preserveUnknown' => true,
            'variables' => [],
        ]);

        self::assertSame('${DB_HOST}', $config->getString('db.host'));
    }

    public function testResolvePlaceholdersTreatsEmptyValueAsMissingWhenAllowEmptyFalse(): void
    {
        $config = new Config([
            'db' => [
                'user' => '${DB_USER:?DB user is required}',
            ],
        ], '.');

        $this->expectException(ERuntimeError::class);
        $this->expectExceptionMessage('DB user is required');

        $config->resolvePlaceholders([
            'allowEmpty' => false,
            'variables' => ['DB_USER' => ''],
        ]);
    }

}
