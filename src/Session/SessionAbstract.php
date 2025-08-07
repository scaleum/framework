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

namespace Scaleum\Session;

use Scaleum\Core\DependencyInjection\Framework;
use Scaleum\Core\KernelEvents;
use Scaleum\Events\EventManagerInterface;
use Scaleum\Http\CookieManager;
use Scaleum\Logger\LoggerChannelTrait;
use Scaleum\Services\ServiceLocator;
use Scaleum\Stdlib\Base\Hydrator;
use Scaleum\Stdlib\Exceptions\ERuntimeError;
use Scaleum\Stdlib\Helpers\HttpHelper;
use Scaleum\Stdlib\Helpers\UniqueHelper;
use Scaleum\Stdlib\SAPI\Explorer;
use Scaleum\Stdlib\SAPI\SapiMode;

/**
 * SessionAbstract
 *
 * @author Maxim Kirichenko <kirichenko.maxim@gmail.com>
 */
abstract class SessionAbstract extends Hydrator implements SessionInterface {
    use LoggerChannelTrait;

    protected const EXPIRATION_DEFAULT       = 3600;
    protected array $data                    = [];
    protected int $expiration                = self::EXPIRATION_DEFAULT;
    protected bool $destroyOnClose           = false;
    protected string $name                   = 'SESSION_ID';
    protected string $timeReference          = 'time';
    protected bool $logging                  = true;
    protected ?EventManagerInterface $events = null;
    protected ?CookieManager $cookies        = null;
    protected string $id;
    ///////////////////////////////////////////////////////////////////////////
    abstract protected function read(): array;
    abstract protected function write(array $data): void;
    abstract protected function delete(): void;
    abstract public function cleanup(): void;
    ///////////////////////////////////////////////////////////////////////////

    public function __construct(array $config = []) {
        parent::__construct($config);

        $this->getEvents()->on(KernelEvents::FINISH, function () {
            // FIXME - переделать, т.к. ссылка на SESSION_UPDATED подразумевает совместную работу с SSE
            if (! defined('SESSION_UPDATED')) {
                $this->cleanup();
                $this->update();

                define('SESSION_UPDATED', true);
            }
        }, 0);

        $this->open($this->name);
    }

    public function getLoggerChannel(): string {
        return 'kernel';
    }

    public function getEvents() {
        if ($this->events === null) {
            if (! ($events = ServiceLocator::get(Framework::SVC_EVENTS, null)) instanceof EventManagerInterface) {
                throw new ERuntimeError(
                    sprintf(
                        "Events service `%s` is not found or is not an instance of `%a`, given `%s`.",
                        Framework::SVC_EVENTS,
                        EventManagerInterface::class,
                        is_object($events) ? get_class($events) : gettype($events)
                    )
                );
            }
            $this->events = $events;
        }

        return $this->events;
    }

    public function setEvents(EventManagerInterface $events): static {
        $this->events = $events;
        return $this;
    }

    private function getAnchor(string $key, mixed $default = null): mixed {
        return $this->getCookies()?->get($key, $default);
    }

    private function setAnchor(string $key, mixed $value = null): mixed {
        if ($this->getCookies()?->setExpire($this->getTimestamp($this->expiration))->set($key, $value) === true) {
            return true;
        }

        return false;
    }

    public function isValid(): bool {
        if ((int) $this->get('last_activity', 0) < $this->getTimestamp() - $this->getExpiration()) {
            ! $this->logging || $this->debug('Session has expired');
            return false;
        }

        if ($this->get('user_ip') != HttpHelper::getUserIP()) {
            ! $this->logging || $this->debug('Session has incorrect data(user IP), and will be flushed');
            return false;
        }

        return true;
    }

    public function open($name): bool {

        if (($name == $this->name) && ! empty($this->data)) {
            $this->debug('Session is already opened...synchronization');

            // FIXME - if session is already opened, synchronize for correct work of SSE
            // return true;
        }

        ! $this->logging || $this->debug('Session opening...');

        // if session_id not found - open new
        if ($sessionNotExists = ($id = $this->getAnchor($this->name, false)) === false) {
            ! $this->logging || $this->debug('Session not found, open new');
            $id = UniqueHelper::getUniqueID(UniqueHelper::getUniquePrefix() . HttpHelper::getUserIP());
        }

        $this->name = $name;
        $this->id   = $id;
        $this->data = $this->read();

        // if session loaded, verify execute
        if (! $this->isValid()) {
            $this->update(true);
            return false;
        }

        ! $this->logging || $this->debug('Session has successfully loaded');

        // update if session is new
        if ($sessionNotExists === true) {
            $this->update();
        }

        return true;
    }

    public function close(): static {
        $this->getCookies()?->delete($this->name);
        return $this;
    }

    protected function update(bool $flush = false) {
        // flush all data
        if ($flush === true) {
            ! $this->logging || $this->debug('Session was flushed');
            $this->data = [];
        }

        // Update system information
        $this->data['session_id'] = $this->id;
        $this->data['user_ip']    = HttpHelper::getUserIP();
        $this->data['user_agent'] = HttpHelper::getUserAgent();

        // Update the tag of the last activity if the request has no the header "X-Requested-Mode"
        // or this header is not set as "service" - AJAX request
        if ($flush === true || strtolower($_SERVER['X-Requested-Mode'] ?? '') != 'service') {
            $this->data['last_activity'] = $this->getTimestamp();
        }

        if (in_array(Explorer::getTypeFamily(), [SapiMode::HTTP, SapiMode::UNIVERSAL])) {
            $this->write($this->data); // Update session data
        }

        $this->setAnchor($this->name, $this->id); // Refresh cookie info
        ! $this->logging || $this->debug('Session has successfully updated');
    }

    protected function getTimestamp(int $shift = 0): int {
        switch (strtolower($this->timeReference)) {
        case 'gmt':
            $now    = time() + $shift;
            $result = (int) mktime((int) gmdate("H", $now), (int) gmdate("i", $now), (int) gmdate("s", $now), (int) gmdate("m", $now), (int) gmdate("d", $now), (int) gmdate("Y", $now));
            break;
        default:
            $result = time() + $shift;
            break;
        }

        return $result;
    }

    public function has(int | string $var): bool {
        return array_key_exists($var, $this->data);
    }

    public function get(int | string $var, mixed $default = false): mixed {
        if ($this->has($var)) {
            return $this->data[$var];
        }
        return $default;
    }

    public function getByPrefix(?string $prefix = null): array {
        if ($prefix !== null && $prefix !== '') {
            $result = [];
            foreach ($this->data as $key => $value) {
                if (str_starts_with($key, $prefix)) {
                    $result[$key] = $value;
                }
            }
            return $result;
        }

        return $this->data;
    }

    public function set(int | string $var, mixed $value = null, bool $updateImmediately = false): static {
        if (! is_array($var)) {
            $var = [$var => $value];
        }

        foreach ($var as $key => $val) {
            if (! is_numeric($key)) {
                switch ($val) {
                case NULL:
                    unset($this->data[$key]);
                    break;
                default:
                    $this->data[$key] = $val;
                    break;
                }
            }
        }

        if ($updateImmediately == true) {
            $this->update();
        }

        return $this;
    }

    public function remove(string $key, bool $updateImmediately = true): static {
        unset($this->data[$key]);
        if ($updateImmediately == true) {
            $this->update();
        }
        return $this;
    }

    public function removeByPrefix(string $prefix, bool $updateImmediately = false): static {
        if ($prefix !== null && $prefix !== '') {
            foreach ($this->data as $key => $value) {
                if (str_starts_with($key, $prefix)) {
                    unset($this->data[$key]);
                }
            }
        }

        if ($updateImmediately == true) {
            $this->update();
        }
        return $this;
    }

    public function clear(bool $updateImmediately = false): static {
        $this->data = [];

        if ($updateImmediately == true) {
            $this->update();
        }
        return $this;
    }

    public function getExpiration() {
        return $this->expiration;
    }

    public function setExpiration(int $expiration) {
        $this->expiration = $expiration > 0 ? $expiration : self::EXPIRATION_DEFAULT;
        return $this;
    }

    /**
     * Get the value of cookies
     */
    public function getCookies(): ?CookieManager {
        if ($this->cookies === null) {
            $this->cookies = new CookieManager();
        }

        return $this->cookies;
    }

    /**
     * Set the value of cookies
     *
     * @return  self
     */
    public function setCookies(array | CookieManager $cookies): static
    {
        if (is_array($cookies)) {
            $cookies = self::createInstance([ ...$cookies, 'class' => CookieManager::class]);
        }
        $this->cookies = $cookies;
        return $this;
    }
}
/** End of SessionAbstract **/