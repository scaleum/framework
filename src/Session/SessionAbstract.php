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
abstract class SessionAbstract extends Hydrator {
    use LoggerChannelTrait;
    protected const EXPIRATION_DEFAULT = 3600;
    protected const MAX_ANCHOR_LEN     = 32;
    protected array $data              = [];
    /**
     * Lifetime session(in seconds)
     * @var int
     */
    protected int $expiration = self::EXPIRATION_DEFAULT;
    /**
     * Destroy session cookie on close
     * @var bool
     */
    protected bool $destroyOnClose = false;
    /**
     * Session ID
     * @var string
     */
    protected string $id;
    /**
     * Session cookie anchor
     * @var string
     */
    protected string $name = 'SESSION_ID';
    /**
     * Salt for encoding cookie var
     * @var string
     */
    protected string $salt = 'f6cc260e-17df-4b78-a54a-64d02766adf9';
    /**
     * Time format
     * @var string ['time'|'gmt']
     */
    protected string $timeReference = 'time';
    protected string $cookieDomain  = '';
    protected bool $cookieEncode    = false;
    protected bool $cookieHttpOnly  = false;
    protected string $cookiePath    = '/';
    protected bool $cookieSecure    = false;
    protected bool $logging         = true;
    protected ?EventManagerInterface $events;

    ///////////////////////////////////////////////////////////////////////////
    abstract protected function read(): array;
    abstract protected function write(array $data): void;
    abstract protected function delete(): void;
    abstract public function cleanup(): void;
    ///////////////////////////////////////////////////////////////////////////

    public function __construct(array $config = []) {
        parent::__construct($config);

        $this->getEvents()->on(KernelEvents::START, function () {
            // $this->debug('Session started');
            $this->open($this->name);
        }, -9900);
        $this->getEvents()->on(KernelEvents::FINISH, function () {
            // $this->debug('Session finished');
            if (! defined('SESSION_UPDATED')) {
                $this->cleanup();
                $this->update();

                define('SESSION_UPDATED', true);
            }
        }, -9999);
    }
    public function getLoggerChannel(): string {
        return 'kernel';
    }

    public function getEvents() {
        if ($this->events === null) {
            if (! ($events = ServiceLocator::get(Framework::SVC_EVENTS, null)) instanceof EventManagerInterface) {
                throw new ERuntimeError(
                    sprintf(
                        "Events service `%s` not found or is not an instance of `%a`, given `%s`.",
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
        if (! isset($_COOKIE[$key])) {
            return $default;
        }

        $value = $_COOKIE[$key];

        // decode
        if ($this->cookieEncode == true) {
            $value = base64_decode(str_pad(strtr($value, '-_', '+/'), strlen($value) % 4, '=', STR_PAD_RIGHT));
            $hash  = substr($value, strlen($value) - self::MAX_ANCHOR_LEN); // get last 32 chars
            $value = substr($value, 0, strlen($value) - self::MAX_ANCHOR_LEN);

            // Does the md5 hash match?  This is to prevent manipulation of session data in user space
            if ($hash !== md5("$value{$this->salt}")) {
                return $default;
            }
        }

        // check length
        if (strlen($value) > self::MAX_ANCHOR_LEN) {
            $value = substr($value, 0, self::MAX_ANCHOR_LEN);
        }

        return $value;
    }

    private function setAnchor(string $key, mixed $value = null): mixed {
        if (! headers_sent()) {
            if (is_array($value) || is_object($value)) {
                $value = json_encode($value);
            }

            if ($this->cookieEncode == true) {
                $value = (string) $value . md5("$value{$this->salt}");
                $value = rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
            }

            $cookieExpire = $this->getTimestamp($this->expiration);
            setcookie($key, $value, $cookieExpire, $this->cookiePath, $this->cookieDomain, $this->cookieSecure, $this->cookieHttpOnly);

            // Hack: only for current session
            $_COOKIE[$key] = $value;

            return $value;
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

    public function open($name) {

        if (($name == $this->name) && ! empty($this->data)) {
            $this->debug('Session is already opened...synchronization');

            // if session is already opened, synchronize for correct work of SSE
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

    public function close() {
        setcookie($this->name, '', $this->getTimestamp() - $this->expiration, $this->cookiePath, $this->cookieDomain, $this->cookieSecure, $this->cookieHttpOnly);
        unset($_COOKIE[$this->name]);
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

    public function set(int | string $var, mixed $value = null, bool $updateImmediately = true) {
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
    }

    public function getExpiration() {
        return $this->expiration;
    }

    public function setExpiration(int $expiration) {
        $this->expiration = $expiration > 0 ? $expiration : self::EXPIRATION_DEFAULT;
        return $this;
    }
}
/** End of SessionAbstract **/