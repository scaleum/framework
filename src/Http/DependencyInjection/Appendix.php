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
namespace Scaleum\Http\DependencyInjection;

use Psr\Container\ContainerInterface;
use Scaleum\DependencyInjection\Container;
use Scaleum\DependencyInjection\Contracts\ConfiguratorInterface;
use Scaleum\Http\RequestHandler;

/**
 * Appendix
 *
 * @author Maxim Kirichenko <kirichenko.maxim@gmail.com>
 */
class Appendix implements ConfiguratorInterface {
    public function configure(Container $container): void {
        $container
            ->addDefinitions([
                RequestHandler::class => fn(ContainerInterface $c) => new RequestHandler($c),
                'app.handler'         => RequestHandler::class,
            ]);
    }
}
/** End of Appendix **/