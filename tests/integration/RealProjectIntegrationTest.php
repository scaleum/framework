<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class RealProjectIntegrationTest extends TestCase
{
    /**
     * @var string[]
     */
    private array $workingDirectories = [];

    protected function tearDown(): void
    {
        foreach ($this->workingDirectories as $directory) {
            $this->removeDirectory($directory);
        }

        $this->workingDirectories = [];
    }

    public function testFrameworkCanBeInstalledAsSymlinkedVendorPackage(): void
    {
        $frameworkRoot = dirname(__DIR__, 2);
        $projectDirectory = $this->createTempDirectory();
        $composerCommand = $this->resolveComposerCommand($frameworkRoot);

        if ($composerCommand === null) {
            $this->markTestSkipped('Composer is not available. Install composer or set COMPOSER_BINARY to run this integration test.');
        }

        $this->createFixtureComposerJson($projectDirectory, $frameworkRoot);

        [$installCode, $installOutput] = $this->runCommand(
            [...$composerCommand, 'install', '--no-interaction', '--no-progress', '--prefer-source'],
            $projectDirectory
        );

        $this->assertSame(0, $installCode, "Composer install failed:\n" . $installOutput);

        $installedFrameworkPath = $projectDirectory . '/vendor/scaleum/framework';
        $this->assertDirectoryExists($installedFrameworkPath);

        $realInstalledPath = realpath($installedFrameworkPath);
        $realFrameworkPath = realpath($frameworkRoot);

        $this->assertNotFalse($realInstalledPath);
        $this->assertNotFalse($realFrameworkPath);
        $this->assertSame(
            $realFrameworkPath,
            $realInstalledPath,
            'vendor/scaleum/framework must resolve to the local framework sources.'
        );

        file_put_contents(
            $projectDirectory . '/probe.php',
            "<?php\nrequire __DIR__ . '/vendor/autoload.php';\necho \\Scaleum\\Core\\Version::get();\n"
        );

        [$probeCode, $probeOutput] = $this->runCommand([PHP_BINARY, 'probe.php'], $projectDirectory);

        $this->assertSame(0, $probeCode, "Probe script failed:\n" . $probeOutput);
        $this->assertSame(Scaleum\Core\Version::get(), trim($probeOutput));
    }

    private function createTempDirectory(): string
    {
        $directory = rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR)
            . DIRECTORY_SEPARATOR
            . 'scaleum-int-test-'
            . bin2hex(random_bytes(8));

        mkdir($directory, 0777, true);
        $this->workingDirectories[] = $directory;

        return $directory;
    }

    private function createFixtureComposerJson(string $projectDirectory, string $frameworkRoot): void
    {
        $composerJson = [
            'name' => 'scaleum/integration-fixture',
            'type' => 'project',
            'require' => [
                'php' => '>=8.1',
                'scaleum/framework' => '*',
            ],
            'repositories' => [
                [
                    'type' => 'path',
                    'url' => $frameworkRoot,
                    'options' => [
                        'symlink' => true,
                    ],
                ],
            ],
            'minimum-stability' => 'dev',
            'prefer-stable' => true,
        ];

        file_put_contents(
            $projectDirectory . '/composer.json',
            json_encode($composerJson, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL
        );
    }

    /**
     * @return string[]|null
     */
    private function resolveComposerCommand(string $frameworkRoot): ?array
    {
        $composerBinary = getenv('COMPOSER_BINARY');

        if ($composerBinary !== false && $composerBinary !== '') {
            $command = str_ends_with($composerBinary, '.phar')
                ? [PHP_BINARY, $composerBinary]
                : [$composerBinary];

            [$code] = $this->runCommand([...$command, '--version'], $frameworkRoot);
            if ($code === 0) {
                return $command;
            }
        }

        $localComposerPhar = $frameworkRoot . '/composer.phar';
        if (is_file($localComposerPhar)) {
            return [PHP_BINARY, $localComposerPhar];
        }

        $candidates = DIRECTORY_SEPARATOR === '\\'
            ? [
                ['cmd', '/C', 'composer'],
                ['composer'],
            ]
            : [
                ['composer'],
            ];

        foreach ($candidates as $candidate) {
            [$code] = $this->runCommand([...$candidate, '--version'], $frameworkRoot);
            if ($code === 0) {
                return $candidate;
            }
        }

        return null;
    }

    /**
     * @param string[] $command
     *
     * @return array{0:int,1:string}
     */
    private function runCommand(array $command, string $cwd): array
    {
        $descriptors = [
            1 => ['pipe', 'w'],
            2 => ['pipe', 'w'],
        ];

        $process = proc_open($command, $descriptors, $pipes, $cwd);

        if (!is_resource($process)) {
            return [1, 'Could not start process.'];
        }

        $stdout = stream_get_contents($pipes[1]);
        fclose($pipes[1]);

        $stderr = stream_get_contents($pipes[2]);
        fclose($pipes[2]);

        $code = proc_close($process);

        return [$code, trim($stdout . PHP_EOL . $stderr)];
    }

    private function removeDirectory(string $directory): void
    {
        if (!is_dir($directory)) {
            return;
        }

        $items = scandir($directory);
        if ($items === false) {
            return;
        }

        foreach ($items as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }

            $path = $directory . DIRECTORY_SEPARATOR . $item;

            if (is_link($path) || is_file($path)) {
                unlink($path);
                continue;
            }

            if (is_dir($path)) {
                $this->removeDirectory($path);
            }
        }

        rmdir($directory);
    }
}
