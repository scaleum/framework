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

use Scaleum\Services\ServiceManager;

/**
 * LoaderManager
 *
 * @author Maxim Kirichenko <kirichenko.maxim@gmail.com>
 */
class LoaderDispatcher extends ServiceManager {
    protected array $invokableClasses = [
        'phparray' => Loader\PhpArray::class,
        'php'      => Loader\PhpArray::class,
        'json'     => Loader\Json::class,
        'ini'      => Loader\Ini::class,
        // 'yaml' => Loader\Yaml::class,
        // 'yml' => Loader\Yaml::class,
        'xml'      => Loader\Xml::class,
    ];

}
/** End of LoaderManager **/