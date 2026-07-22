<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Psr\Log\InvalidArgumentException;
use Psr\Log\NullLogger;
use Scaleum\Logger\LoggerGateway;
use Scaleum\Logger\LoggerManager;

class LoggerGatewayTest extends TestCase
{
    protected function tearDown(): void
    {
        LoggerGateway::resetProvider();
        LoggerGateway::strictModeOn();
    }

    public function testGetLoggerReturnsNullForMissingChannel(): void
    {
        LoggerGateway::setProvider(new LoggerManager());

        $this->assertNull(LoggerGateway::getLogger('missing'));
    }

    public function testManagerStillThrowsForMissingChannel(): void
    {
        $manager = new LoggerManager();

        $this->expectException(InvalidArgumentException::class);

        $manager->getLogger('missing');
    }

    public function testGetLoggerReturnsRegisteredLogger(): void
    {
        $logger = new NullLogger();
        $manager = new LoggerManager();
        $manager->setLogger('kernel', $logger);
        LoggerGateway::setProvider($manager);

        $this->assertSame($logger, LoggerGateway::getLogger('kernel'));
    }
}
