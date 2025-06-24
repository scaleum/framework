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

use finfo;
use RuntimeException;

class FileHelper {
    // File and Directory Modes
    const FILE_READ_MODE  = 0644;
    const FILE_WRITE_MODE = 0666;
    const DIR_READ_MODE   = 0755;
    const DIR_WRITE_MODE  = 0777;

    // File Stream Modes
    const FOPEN_READ                          = 'rb';
    const FOPEN_READ_WRITE                    = 'r+b';
    const FOPEN_WRITE_CREATE_DESTRUCTIVE      = 'wb';  // truncates existing file
    const FOPEN_READ_WRITE_CREATE_DESTRUCTIVE = 'w+b'; // truncates existing file
    const FOPEN_WRITE_CREATE                  = 'ab';
    const FOPEN_READ_WRITE_CREATE             = 'a+b';
    const FOPEN_WRITE_CREATE_STRICT           = 'xb';
    const FOPEN_READ_WRITE_CREATE_STRICT      = 'x+b';

    /**
     * Deletes a file.
     *
     * @param string $filename The path to the file to be deleted.
     * @return bool True if the file was successfully deleted, false otherwise.
     */
    public static function deleteFile(string $filename) {
        $result = false;
        try {
            $result = unlink($filename);
        } catch (\Exception $e) {
            $result = false;
        }
        return $result;
    }

    /**
     * Deletes files and optionally directories recursively from the specified path.
     *
     * @param string $path The path to the directory where the files should be deleted.
     * @param bool $deleteDir Whether to delete the directories as well. Default is false.
     * @param int $level The recursion level. Default is 0 (no recursion).
     * @return bool Returns true if the files were successfully deleted, false otherwise.
     */
    public static function deleteFiles(string $path, bool $deleteDir = false, int $level = 0): bool {
        // Trim the trailing slash
        $path = rtrim($path, DIRECTORY_SEPARATOR);

        if (! $currentDir = @opendir($path)) {
            return false;
        }

        while (false !== ($filename = @readdir($currentDir))) {
            if ($filename != "." and $filename != "..") {
                if (is_dir($path . DIRECTORY_SEPARATOR . $filename)) {
                    // Ignore empty folders
                    if (substr($filename, 0, 1) != '.') {
                        self::deleteFiles($path . DIRECTORY_SEPARATOR . $filename, $deleteDir, $level + 1);
                    }
                } else {
                    self::deleteFile($path . DIRECTORY_SEPARATOR . $filename);
                }
            }
        }
        @closedir($currentDir);

        if ($deleteDir && ($level > 0)) {
            return @rmdir($path);
        }

        return true;
    }

    /**
     * Retrieves the directories within a given source directory.
     *
     * @param string $path The source directory to retrieve directories from.
     * @param bool $onlyTop (optional) Whether to only retrieve directories from the top level of the source directory. Default is true.
     * @param bool $recursion (optional) Whether to retrieve directories recursively. Default is false.
     * @return mixed An array of directories if successful, false otherwise.
     */
    public static function getDir(string $path, bool $onlyTop = true, bool $recursion = false): mixed {
        static $filedata;
        // Trim the trailing slash
        $path = rtrim($path, DIRECTORY_SEPARATOR);

        $relativePath = $path;

        if ($fp = @opendir($path)) {
            if ($recursion === false) {
                $filedata = [];
                $path     = rtrim(realpath($path), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
            }

            while (false !== ($filename = readdir($fp))) {
                if (@is_dir($path . DIRECTORY_SEPARATOR . $filename) and strncmp($filename, '.', 1) !== 0 and $onlyTop === false) {
                    self::getDir($path . DIRECTORY_SEPARATOR . $filename, $onlyTop, $recursion);
                } elseif (strncmp($filename, '.', 1) !== 0) {
                    $filedata[$filename]                  = self::getFileInfo($path . DIRECTORY_SEPARATOR . $filename);
                    $filedata[$filename]['relative_path'] = $relativePath;
                }
            }

            return $filedata;
        } else {
            return false;
        }
    }

    /**
     * Returns the file extension of the given file.
     *
     * @param string $file The file path or name.
     * @return string The file extension.
     */
    public static function getFileExtension(string $file): string {
        $parts = explode('.', $file);

        return end($parts);
    }

    /**
     * Retrieves information about a file.
     *
     * @param string $file The path to the file.
     * @param array $returnedValues The values to be returned. Default is ['name', 'path', 'size', 'type'].
     * @return array|bool An array containing the requested file information, or false if the file does not exist.
     */
    public static function getFileInfo(string $file, array $returnedValues = ['name', 'path', 'size', 'type']): array | bool {
        $result = [];

        if (! file_exists($file)) {
            return false;
        }

        if (is_string($returnedValues)) {
            $returnedValues = explode(',', $returnedValues);
        }

        foreach ($returnedValues as $key) {
            switch ($key) {
            case 'name':
                $result['name'] = basename($file);
                break;
            case 'path':
                $result['path'] = $file;
                break;
            case 'size':
                $result['size'] = filesize($file);
                break;
            case 'date':
                $result['date'] = filemtime($file);
                break;
            case 'readable':
                $result['readable'] = is_readable($file);
                break;
            case 'writable':
                // There are known problems using is_weritable on IIS.  It may not be reliable - consider fileperms()
                $result['writable'] = is_writable($file);
                break;
            case 'executable':
                $result['executable'] = is_executable($file);
                break;
            case 'fileperms':
                $result['fileperms'] = fileperms($file);
                break;
            case 'type':
                $result['type'] = self::getFileType($file);
                break;
            }
        }

        return $result;
    }

    /**
     * Returns the file type of the given filename.
     *
     * @param string $filename The name of the file.
     * @return string The file type.
     */
    public static function getFileType(string $filename): string {
        if (function_exists('finfo_file')) {
            $finfo = finfo_open(FILEINFO_MIME);
            if (is_resource($finfo)) // It is possible that a FALSE value is returned, if there is no magic MIME database file found on the system
            {
                /** @var finfo $finfo */
                $mime = @finfo_file($finfo, $filename);
                finfo_close($finfo);

                /* According to the comments section of the PHP manual page,
                 * it is possible that this function returns an empty string
                 * for some files (e.g. if they don't exist in the magic MIME database)
                 */
                $regexp = '/^([a-z\-]+\/[a-z0-9\-\.\+]+)(;\s.+)?$/';
                if (is_string($mime) && preg_match($regexp, $mime, $matches)) {
                    return $matches[1];
                }
            }
        }

        // Fall back to the deprecated mime_content_type(), if available (still better than $_FILES[$field]['type'])
        if (function_exists('mime_content_type')) {
            $mime = @mime_content_type($filename);
            if (strlen($mime) > 0) // It's possible that mime_content_type() returns FALSE or an empty string
            {
                return $mime;
            }
        }

        return 'application/octet-stream';
    }

    /**
     * Retrieves a list of files from a specified directory.
     *
     * @param string $sourceDir The directory path to retrieve files from.
     * @param bool $includePath (Optional) Whether to include the full path of each file in the result. Default is false.
     * @param bool $recursion (Optional) Whether to search for files recursively in subdirectories. Default is false.
     * @return array|bool An array of file paths if successful, or false if the directory does not exist or is not readable.
     */
    public static function getFiles(string $sourceDir, bool $includePath = false, bool $recursion = false): array | bool {
        static $result = [];

        if ($fp = @opendir($sourceDir)) {
            // reset the array and make sure $sourceDir has a trailing slash on the initial call
            if ($recursion === false) {
                $result    = [];
                $sourceDir = rtrim(realpath($sourceDir), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
            }

            while (false !== ($file = readdir($fp))) {
                if (@is_dir($sourceDir . $file) && strncmp($file, '.', 1) !== 0) {
                    self::getFiles($sourceDir . $file . DIRECTORY_SEPARATOR, $includePath, true);
                } elseif (strncmp($file, '.', 1) !== 0) {
                    $result[] = ($includePath == true) ? "$sourceDir$file" : $file;
                }
            }

            return $result;
        } else {
            return false;
        }
    }

    /**
     * Checks if a file is really writable.
     *
     * This method checks if the specified file is writable by verifying both the file's write permission
     * and the write permission of its parent directory.
     *
     * @param string $file The path to the file.
     * @return bool Returns true if the file is really writable, false otherwise.
     */
    public static function isReallyWritable(string $file): bool {
        // If we're on a Unix server with safe_mode off we call is_writable
        if (DIRECTORY_SEPARATOR == '/' and @ini_get("safe_mode") == false) {
            return is_writable($file);
        }

        // For windows servers and safe_mode "on" installations we'll actually
        // write a file then read it.  Bah...
        if (is_dir($file)) {
            $file = rtrim($file, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . md5(mt_rand(1, 100) . mt_rand(1, 100));

            if (($fp = @fopen($file, self::FOPEN_WRITE_CREATE)) === false) {
                return false;
            }

            fclose($fp);
            @chmod($file, self::DIR_WRITE_MODE);
            @unlink($file);

            return true;
        } elseif (! is_file($file) or ($fp = @fopen($file, self::FOPEN_WRITE_CREATE)) === false) {
            return false;
        }

        fclose($fp);

        return true;
    }

    /**
     * Converts octal permissions to a human-readable format.
     *
     * @param int $perms The octal permissions to convert.
     * @return string The human-readable format of the permissions.
     */
    public static function octalPermissions($perms) {
        return substr(sprintf('%o', $perms), -3);
    }

    /**
     * Prepares a filename by performing necessary modifications.
     *
     * @param string $filename The original filename.
     * @return string The modified filename.
     */
    public static function prepFilename(string $filename, bool $normalize = true): string {
        $parts   = explode(DIRECTORY_SEPARATOR, trim($filename, DIRECTORY_SEPARATOR));
        $file    = array_pop($parts);
        $file    = pathinfo($file, PATHINFO_EXTENSION) ? $file : $file . '.' . self::getFileExtension(__FILE__);
        $parts[] = $file;

        $file = PathHelper::join(...$parts);

        if (function_exists('realpath') && $normalize) {
            if ($realpath = realpath($file)) {
                $file = $realpath;
            }
        }

        return $file;
    }

    /**
     * Prepares the given location string.
     *
     * @param string $location The location string to be prepared.
     * @return string The prepared location string.
     */
    public static function prepLocation(string $location): string {
        return str_replace(['\\', '/'], DIRECTORY_SEPARATOR, $location);
    }

    /**
     * Prepares a file path.
     *
     * This method takes a file path as input and prepares it for further processing.
     *
     * @param string $path The file path to be prepared.
     * @param bool $normalize Whether to normalize the file path. Defaults to true.
     * @return string The prepared file path.
     */
    public static function prepPath(string $path, bool $normalize = true): string {
        if (function_exists('realpath') && $normalize) {
            if ($realpath = realpath($path)) {
                $path = $realpath;
            }
        }

        $path = rtrim(self::prepLocation($path), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
        $isUnix = ProcessHelper::isUnixOS();
        if ($isUnix && ! str_starts_with($path, DIRECTORY_SEPARATOR)) {
            $path = DIRECTORY_SEPARATOR . $path;
        }        
        return $path;
    }

    /**
     * Reads the contents of a file.
     *
     * @param string $file The path to the file to be read.
     * @return bool|string The contents of the file as a string, or false if the file cannot be read.
     */
    public static function readFile(string $file): bool | string {
        if (! file_exists($file)) {
            return false;
        }

        if (function_exists('file_get_contents')) {
            return file_get_contents($file);
        }

        if (! $fp = @fopen($file, self::FOPEN_READ)) {
            return false;
        }

        flock($fp, LOCK_SH);

        $data = '';
        if (filesize($file) > 0) {
            $data = &fread($fp, filesize($file));
        }

        flock($fp, LOCK_UN);
        fclose($fp);

        return $data;
    }

    /**
     * Converts numeric file permissions to symbolic format.
     *
     * @param mixed $perms The numeric file permissions to convert.
     * @return string The symbolic representation of the file permissions.
     */
    public static function symbolicPermissions(mixed $perms): string {
        if (($perms & 0xC000) == 0xC000) {
            $symbolic = 's'; // Socket
        } elseif (($perms & 0xA000) == 0xA000) {
            $symbolic = 'l'; // Symbolic Link
        } elseif (($perms & 0x8000) == 0x8000) {
            $symbolic = '-'; // Regular
        } elseif (($perms & 0x6000) == 0x6000) {
            $symbolic = 'b'; // Block special
        } elseif (($perms & 0x4000) == 0x4000) {
            $symbolic = 'd'; // Directory
        } elseif (($perms & 0x2000) == 0x2000) {
            $symbolic = 'c'; // Character special
        } elseif (($perms & 0x1000) == 0x1000) {
            $symbolic = 'p'; // FIFO pipe
        } else {
            $symbolic = 'u'; // Unknown
        }

        // Owner
        $symbolic .= (($perms & 0x0100) ? 'r' : '-');
        $symbolic .= (($perms & 0x0080) ? 'w' : '-');
        $symbolic .= (($perms & 0x0040) ? (($perms & 0x0800) ? 's' : 'x') : (($perms & 0x0800) ? 'S' : '-'));

        // Group
        $symbolic .= (($perms & 0x0020) ? 'r' : '-');
        $symbolic .= (($perms & 0x0010) ? 'w' : '-');
        $symbolic .= (($perms & 0x0008) ? (($perms & 0x0400) ? 's' : 'x') : (($perms & 0x0400) ? 'S' : '-'));

        // World
        $symbolic .= (($perms & 0x0004) ? 'r' : '-');
        $symbolic .= (($perms & 0x0002) ? 'w' : '-');
        $symbolic .= (($perms & 0x0001) ? (($perms & 0x0200) ? 't' : 'x') : (($perms & 0x0200) ? 'T' : '-'));

        return $symbolic;
    }

    /**
     * Writes data to a file.
     *
     * @param string $file The path to the file.
     * @param mixed $data The data to be written to the file.
     * @param string $mode The mode in which the file should be opened. Default is self::FOPEN_WRITE_CREATE_DESTRUCTIVE.
     * @return bool Returns true on success, false on failure.
     */
    public static function writeFile(string $file, mixed $data, string $mode = self::FOPEN_WRITE_CREATE_DESTRUCTIVE): bool {
        if (! is_dir($dir = dirname($file))) {
            if (@mkdir($dir, self::DIR_WRITE_MODE, true) == false) {
                throw new RuntimeException(sprintf('%s: failed to create dir "%s"', __METHOD__, $dir));
            }
        }

        if (! $fp = @fopen($file, $mode)) {
            throw new RuntimeException(sprintf('%s: failed to open file "%s"', __METHOD__, $file));
        }

        flock($fp, LOCK_EX);
        fwrite($fp, $data);
        fflush($fp);
        flock($fp, LOCK_UN);
        fclose($fp);

        return true;
    }

    /**
     * Flushes the contents of a file.
     *
     * @param string $file The path to the file to be flushed.
     * @return void
     */
    public static function flushFile(string $file): void {

        if (! is_dir($dir = dirname($file))) {
            if (@mkdir($dir, self::DIR_WRITE_MODE, true) == false) {
                throw new RuntimeException(sprintf('%s: failed to create dir "%s"', __METHOD__, $dir));
            }
        }

        if ($fp = @fopen($file, self::FOPEN_WRITE_CREATE_DESTRUCTIVE)) {
            fclose($fp);
        }
    }
}

/* End of file FileHelper.php */
