<?php
declare (strict_types = 1);
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
class LoaderResolver {
    protected static ?LoaderDispatcher $loaders = null;
    protected static array $extensions          = [
        'php'      => 'php',
        'phparray' => 'php',
        'ini'      => 'ini',
        'json'     => 'json',
        'xml'      => 'xml',
    ];

    public function __construct(
        protected ?string $env = null,
    ) {}

    public function fromDir(string $path) {
        $result     = [];
        $path       = FileHelper::prepPath($path);
        $extensions = implode(',', array_keys(static::$extensions));
        $files      = glob("$path/*.{{$extensions}}", GLOB_BRACE);
        foreach ($files as $file) {
            $result = ArrayHelper::merge($result, $this->fromFile($file));
        }
        return $result;
    }

    public function fromFile(string $file): array {
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

    protected static function getDispatcher(): LoaderDispatcher {
        if (self::$loaders === null) {
            self::$loaders = new LoaderDispatcher();
        }

        return self::$loaders;
    }
}
/** End of ConfigFactory **/