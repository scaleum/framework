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
        'phparray' => Loaders\PhpArray::class,
        'php'      => Loaders\PhpArray::class,
        'json'     => Loaders\Json::class,
        'ini'      => Loaders\Ini::class,
        'xml'      => Loaders\Xml::class,
    ];
}
/** End of LoaderManager **/