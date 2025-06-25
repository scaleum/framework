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

namespace Scaleum\Console;

use Scaleum\Core\Contracts\HandlerInterface;
use Scaleum\Core\KernelAbstract;

/**
 * Application
 *
 * @author Maxim Kirichenko <kirichenko.maxim@gmail.com>
 */
class Application extends KernelAbstract {
    protected ?HandlerInterface $handler = null;

    public function bootstrap(array $config = []): self {
        $this->getRegistry()->set('kernel.configurators', [
            new DependencyInjection\Appendix(),
            new DependencyInjection\Commands(),
        ]);

        parent::bootstrap($config);
        return $this;
    }

    public function getHandler(): HandlerInterface {
        if ($this->handler === null) {
            $this->handler = $this->getContainer()->get('app.handler');
        }
        return $this->handler;
    }
}
/** End of Application **/