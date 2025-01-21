<?php
/**
 * This file is part of Scaleum\Stdlib.
 *
 * (C) 2009-2025 Maxim Kirichenko <kirichenko.maxim@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Scaleum\Stdlib\Helper;

/**
 * Class PathHelper
 */
class PathHelper {
    /**
     * Returns the path excluding matching segments($overlap)
     *
     * @param string      $path
     * @param mixed|string $overlap
     *
     * @return array|string
     */
    public static function overlapPath(string $path, mixed $overlap = null) {
        if ($overlap == null || (! is_string($path) || ! is_string($overlap))) {
            return $path;
        }

        $path    = explode('/', str_replace("\\", "/", $path));
        $overlap = explode('/', str_replace("\\", "/", $overlap));

        return '/' . implode('/', array_diff_assoc($path, $overlap));
    }

    /**
     *  Returns path to $to relative to $path
     *  Example:
     *  $a="/home/a.php";
     *  $b="/home/root/b/b.php";
     *  echo self::relativePath($a,$b), PHP_EOL;  // ./root/b/b.php
     *
     *  $a="/home/apache/a/a.php";
     *  $b="/home/root/b/b.php";
     *  echo self::relativePath($a,$b), PHP_EOL; // ../../root/b/b.php
     *
     * @param string $path
     * @param string $to
     *
     * @return string
     */
    public static function relativePath(string $path, string $to) {
        // some compatibility fixes for Windows paths
        $path = is_dir($path) ? rtrim($path, '\/') . '/' : $path;
        $to   = is_dir($to) ? rtrim($to, '\/') . '/' : $to;
        $path = str_replace('\\', '/', $path);
        $to   = str_replace('\\', '/', $to);

        $path    = explode('/', $path);
        $to      = explode('/', $to);
        $relPath = $to;

        foreach ($path as $depth => $dir) {
            // find first non-matching dir
            if ($dir === $to[$depth]) {
                // ignore this directory
                array_shift($relPath);
            } else {
                // get number of remaining dirs to $from
                $remaining = count($path) - $depth;
                if ($remaining > 1) {
                    // add traversals up to first matching dir
                    $padLength = (count($relPath) + $remaining - 1) * -1;
                    $relPath   = array_pad($relPath, $padLength, '..');
                    break;
                } else {
                    $relPath[0] = './' . $relPath[0];
                }
            }
        }

        return implode('/', $relPath);
    }

    public static function join(...$parts): string {
        $parts = array_map(fn($part) => trim($part, '/'), $parts);
        return implode(DIRECTORY_SEPARATOR, $parts);
    }

    public static function getScriptDir(): string {
        $file = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS)[count(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS)) - 1]['file'];
        return dirname($file);
    }
}

/* End of file PathHelper.php */
