<?php

declare(strict_types=1);
/**
 * This file is part of Scaleum Framework.
 *
 * (C) 2009-2025 Maxim Kirichenko <kirichenko.maxim@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Scaleum\Config;

use Scaleum\Config\Loaders\LoaderInterface;
use Scaleum\Stdlib\Exceptions\ERuntimeError;
use Scaleum\Stdlib\Helpers\ArrayHelper;
use Scaleum\Stdlib\Helpers\FileHelper;
use Scaleum\Stdlib\Helpers\PathHelper;

/**
 * ConfigFactory
 *
 * @author Maxim Kirichenko <kirichenko.maxim@gmail.com>
 */
class LoaderResolver
{
    protected static ?LoaderDispatcher $loaders = null;

    protected static array $extensions = [
        'php'  => 'php',
        'ini'  => 'ini',
        'json' => 'json',
        'xml'  => 'xml',
    ];

    public function __construct(
        protected ?string $env = null,
    ) {}

    public function getFiles(string $path): array
    {
        $path       = FileHelper::prepPath($path);
        $extensions = implode(',', array_keys(static::$extensions));
        $files      = glob("$path/*.{{$extensions}}", GLOB_BRACE);

        return $files ?: [];
    }

    /**
     * Loads configuration files from the specified directory path.
     *
     * Each supported configuration file in the directory is loaded and its contents
     * are merged into a single array. Optionally, files that have already been processed
     * can be tracked using the $ignored array to prevent duplicate loading.
     *
     * Note: Loading configuration with this method is unsafe in the sense that
     * the returned settings will be merged, which may lead to unexpected overrides
     * or conflicts between configuration files.
     *
     * @param string $path The directory path to load configuration files from.
     * @param array|null $ignored Reference to an array of files to ignore or track as loaded.
     * @return array The merged configuration data from all loaded files.
     */
    public function fromDir(string $path,  ?array &$ignored = null): array
    {
        $result = [];
        $files  = $this->getFiles($path);
        foreach ($files as $file) {
            if ($ignored !== null && in_array($file, $ignored)) {
                continue;
            }
            $result = ArrayHelper::merge($result, $this->fromFile($file));
            if ($ignored !== null) {
                $ignored[] = $file;
            }
        }
        return $result;
    }

    /**
     * Loads and returns configuration data from the specified file.
     *
     * @param string $file The path to the configuration file.
     * @return array The configuration data loaded from the file.
     * @throws \RuntimeException If the file cannot be loaded or parsed.
     */
    public function fromFile(string $file): array
    {
        $result   = [];
        $filename = FileHelper::prepFilename($file);
        $ext      = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        if (isset(static::$extensions[$ext])) {
            $loader = static::$extensions[$ext];
            if (! $loader instanceof LoaderInterface) {
                $loader                   = static::getDispatcher()->getService($loader);
                static::$extensions[$ext] = $loader;
            }

            $result = $loader->fromFile($filename);

            # Extend config with environment specific config
            if (! empty($this->env)) {
                $basename = basename($filename);
                $filename = str_replace($basename, PathHelper::join($this->env, $basename), $filename);
                if (file_exists($filename)) {
                    $extended = $loader->fromFile($filename);
                    $result   = ArrayHelper::merge($result, $extended);
                }
            }
        } else {
            throw new ERuntimeError(sprintf(
                'Unsupported config file extension: `.%s`',
                $ext
            ));
        }

        return $result;
    }

    protected static function getDispatcher(): LoaderDispatcher
    {
        if (self::$loaders === null) {
            self::$loaders = new LoaderDispatcher();
        }

        return self::$loaders;
    }
}
/** End of ConfigFactory **/
