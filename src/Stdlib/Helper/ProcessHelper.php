<?php
declare(strict_types=1);
/**
 * This file is part of Scaleum\Stdlib.
 *
 * (C) 2009-2025 Maxim Kirichenko <kirichenko.maxim@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Scaleum\Stdlib\Helper;

class ProcessHelper
{
    /**
     * Executes a command.
     *
     * @param string $command The command to execute.
     * @return void
     */
    public static function execute(string $command)
    {
        if (self::isWinOS()) {
            pclose(popen('start "Run command..." /MIN cmd.exe /C "' . $command . '"', FileHelper::FOPEN_READ));
        } else {
            system($command . ' &');
        }
    }

    public static function getInterpreter()
    {
        $result = 'php.exe';
        if (self::isUnixOS()) {
            $result = '/usr/bin/env php';
        }

        return $result;
    }

    public static function getStarted()
    {
        $result = [];
        if (self::isWinOS()) {
            if (preg_match_all('/"[^\,]+"\,"([\d]+)"\,.*/i', `tasklist /FO "CSV" /NH`, $matches)) {
                $result = $matches[1];
            }
        } else {
            $result = explode(PHP_EOL, `ps -e | gawk '{print $1}'`);
        }

        return $result;
    }

    public static function isStarted(int $pid = null)
    {
        if ($pid === null) {
            $pid = getmypid();
        }

        return in_array($pid, self::getStarted());
    }

    /**
     * Return TRUE if OS is *nix
     * @return bool
     */
    public static function isUnixOS()
    {
        return self::isWinOS() === false;
    }

    /**
     * Return TRUE if OS is Windows
     * @return bool
     */
    public static function isWinOS()
    {
        return strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';
    }
}

/* End of file ProcessHelper.php */
