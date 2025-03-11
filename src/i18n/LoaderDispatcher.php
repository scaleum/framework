<?php
/**
 * @author    Maxim Kirichenko
 * @copyright Copyright (c) 2009-2017 Maxim Kirichenko (kirichenko.maxim@gmail.com)
 * @license   GNU General Public License v3.0 or later
 */

namespace Scaleum\i18n;

use PHPUnit\Event\TestSuite\Loaded;
use PHPUnit\TextUI\XmlConfiguration\Loader;
use Scaleum\Services\ServiceManager;

/**
 * Class LoaderFactory
 * @subpackage Avant\i18n
 */
class LoaderDispatcher extends ServiceManager
{
    protected array $invokableClasses = [
      'gettext'  => Loaders\Gettext::class,
      'ini'      => Loaders\Ini::class,
      'phparray' => Loaders\PhpArray::class,
    ];
}

/* End of file LoaderFactory.php */
