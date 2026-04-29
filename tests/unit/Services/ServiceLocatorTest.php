<?php

declare(strict_types=1);

namespace Scaleum\Tests\Services;

use PHPUnit\Framework\TestCase;
use Scaleum\Services\ServiceLocator;
use Scaleum\Services\ServiceProviderInterface;
use Scaleum\Stdlib\Exceptions\ERuntimeError;

class ServiceLocatorTest extends TestCase
{
    protected function tearDown(): void
    {
        ServiceLocator::resetProvider();
        ServiceLocator::strictModeOn();
    }

    public function testGetProviderThrowsWhenProviderIsNotSet(): void
    {
        ServiceLocator::resetProvider();
        ServiceLocator::strictModeOn();
        $this->expectException(ERuntimeError::class);
        ServiceLocator::getProvider();
    }

    public function testMethodsDelegateToProvider(): void
    {
        $provider = new class implements ServiceProviderInterface {
            public array $services = [
                'foo' => 123,
            ];
            public function getAll(): array
            {
                return $this->services;
            }
            public function getService(string $str, mixed $default = null): mixed
            {
                return $this->services[$str] ?? $default;
            }
            public function hasService(string $str): bool
            {
                return array_key_exists($str, $this->services);
            }
            public function setService(string $str, mixed $definition, bool $override = false): mixed
            {
                $this->services[$str] = $definition;
                return $definition;
            }
        };

        ServiceLocator::setProvider($provider);

        $this->assertSame(123, ServiceLocator::get('foo'));
        $this->assertSame(['foo' => 123], ServiceLocator::getAll());
        $this->assertTrue(ServiceLocator::has('foo'));
        $this->assertSame('bar', ServiceLocator::set('bar', 'bar'));
        $this->assertSame('bar', ServiceLocator::get('bar'));
    }

    public function testNoProviderInNonStrictMode(): void
    {
        ServiceLocator::resetProvider();
        ServiceLocator::strictModeOff();

        $this->assertNull(ServiceLocator::getProvider());
        $this->assertSame('default', ServiceLocator::get('unknown', 'default'));
        $this->assertSame([], ServiceLocator::getAll());
        $this->assertFalse(ServiceLocator::has('anything'));
        $this->assertNull(ServiceLocator::set('foo', 'bar'));
    }
}

?>
