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

use Scaleum\Config\Loader\LoaderInterface;
use Scaleum\Stdlib\Exception\ERuntimeError;
use Scaleum\Stdlib\Helper\FileHelper;
use Scaleum\Stdlib\Helper\PathHelper;

/**
 * ConfigFactory
 *
 * @author Maxim Kirichenko <kirichenko.maxim@gmail.com>
 */
class LoaderResolver {
    protected static ?LoaderDispatcher $loaders = null;
    protected static array $extensions       = [
        'php'      => 'php',
        'phparray' => 'php',
        'ini'      => 'ini',
        'json'     => 'json',
        // 'yaml'        => 'yaml',
        'xml'      => 'xml',
    ];

    public function __construct(
        protected ?string $env = null,
    ) {}

    public function fromFile(string $filename): array {
        // if (! is_file($filename) || ! is_readable($filename)) {
        //     throw new ERuntimeError(sprintf(
        //         "File '%s' doesn't exist or not readable",
        //         $filename
        //     ));
        // }

        $result   = [];
        $filename = FileHelper::prepFilename($filename);
        
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
                    $result   = array_replace_recursive($result, $extended);
                }
            }
        } else {
            throw new ERuntimeError(sprintf(
                'Unsupported config file extension: .%s',
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