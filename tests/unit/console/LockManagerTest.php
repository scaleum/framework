<?php
declare(strict_types=1);
use PHPUnit\Framework\TestCase;
use Scaleum\Console\LockManager;
use Scaleum\Stdlib\Helpers\FileHelper;
use Scaleum\Stdlib\Helpers\ProcessHelper;

class LockManagerTest extends TestCase {
    private string $lockDir;
    private LockManager $lockManager;

    protected function setUp(): void {
        $this->lockDir = __DIR__ . '/.locks/';
        $this->lockManager = new LockManager($this->lockDir);
    }

    protected function tearDown(): void {
        array_map('unlink', glob($this->lockDir . '*.lock'));
        rmdir($this->lockDir);
    }

    public function testLockCreatesLockFile(): void {
        $processName = 'testProcess';
        $lockHandle = $this->lockManager->lock($processName);

        $this->assertNotNull($lockHandle);
        $this->assertFileExists($this->lockDir . $processName . '.lock');

        $this->lockManager->release($lockHandle);
    }

    public function testLockReturnsNullIfAlreadyLocked(): void {
        $processName = 'testProcess';
        $lockHandle1 = $this->lockManager->lock($processName);
        $lockHandle2 = $this->lockManager->lock($processName);

        $this->assertNotNull($lockHandle1);
        $this->assertNull($lockHandle2);

        $this->lockManager->release($lockHandle1);
    }

    public function testReleaseRemovesLock(): void {
        $processName = 'testProcess';
        $lockHandle = $this->lockManager->lock($processName);

        $this->assertNotNull($lockHandle);
        $this->lockManager->release($lockHandle);
        $this->assertFalse($this->lockManager->isLocked($processName));
    }

    public function testCleanupRemovesStaleLocks(): void {
        $processName = 'testProcess';
        $lockHandle = $this->lockManager->lock($processName);

        $this->assertNotNull($lockHandle);
        $this->lockManager->release($lockHandle);

        // Simulate stale lock by writing a non-existent PID
        file_put_contents($this->lockDir . $processName . '.lock', '999999');

        $this->lockManager->cleanup();

        $this->assertFalse($this->lockManager->isLocked($processName));
        $this->assertFileDoesNotExist($this->lockDir . $processName . '.lock');
    }

    public function testIsLockedReturnsCorrectStatus(): void {
        $processName = 'testProcessA';
        $this->assertFalse($this->lockManager->isLocked($processName));

        $lockHandle = $this->lockManager->lock($processName);

        $this->assertNotNull($lockHandle);
        $this->assertTrue($this->lockManager->isLocked($processName));

        $this->lockManager->release($lockHandle);
        $this->assertFalse($this->lockManager->isLocked($processName));
    }
}