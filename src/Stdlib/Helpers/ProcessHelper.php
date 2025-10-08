<?php
declare (strict_types = 1);
/**
 * This file is part of Scaleum\Stdlib.
 *
 * (C) 2009-2025 Maxim Kirichenko <kirichenko.maxim@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Scaleum\Stdlib\Helpers;

class ProcessHelper {
    /**
     * Executes a command.
     *
     * @param string $command The command to execute.
     * @return void
     */
    public static function execute(string $command) {
        if (self::isWinOS()) {
            pclose(popen('start "Run command..." /MIN cmd.exe /C "' . $command . '"', FileHelper::FOPEN_READ));
        } else {
            system($command . ' &');
        }
    }

    public static function getInterpreter() {
        $result = 'php.exe';
        if (self::isUnixOS()) {
            $result = '/usr/bin/env php';
        }

        return $result;
    }

    public static function getStarted(): array {
        $result = [];

        if (self::isWinOS()) {
            $output = `tasklist /FO "CSV" /NH`;
            if (preg_match_all('/"[^"]+","(\d+)"/', $output, $matches)) {
                $result = array_map('intval', $matches[1]); // Приводим PID к числу
            }
        } else {
            $result = array_map('intval', explode(PHP_EOL, trim(`ps -e -o pid=`)));
        }

        return $result;
    }

    public static function isStarted(int $pid = null): bool {
        if ($pid === null) {
            $pid = getmypid();
        }

        return in_array((int) $pid, self::getStarted(), true);
    }

    public static function isPhpProcess(int $pid): bool {
        if (! self::isStarted($pid)) {
            return false; // Процесс уже не существует
        }

        if (self::isWinOS()) {
            $psCmd     = 'powershell -Command "try { (Get-Process -Id ' . (int) $pid . ').Path } catch {}"';
            $outputRaw = shell_exec($psCmd);
            $output    = $outputRaw !== null ? trim($outputRaw) : '';

            $lines       = explode(PHP_EOL, $output);
            $processPath = trim($lines[1] ?? '');
        } else {
            $pidSafe     = escapeshellarg((string) $pid); // Защищаем аргумент
            $output      = trim(`ps -p $pidSafe -o comm= 2>/dev/null`);
            $processPath = trim($output);
        }

        return ! empty($processPath) && stripos($processPath, 'php') !== false;
    }

    /**
     * Return TRUE if OS is *nix
     * @return bool
     */
    public static function isUnixOS() {
        return self::isWinOS() === false;
    }

    /**
     * Return TRUE if OS is Windows
     * @return bool
     */
    public static function isWinOS() {
        return strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';
    }
}

/* End of file ProcessHelper.php */
