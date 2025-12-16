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

use Scaleum\Core\Contracts\HandlerInterface;
use Scaleum\Core\DependencyInjection\Framework;
use Scaleum\Core\KernelEvents;
use Scaleum\Events\Event;
use Scaleum\Events\EventManagerInterface;
use Scaleum\Http\InboundRequest;
use Scaleum\Http\OutboundResponse;
use Scaleum\Logger\LoggerChannelTrait;
use Scaleum\Services\ServiceLocator;
use Scaleum\Session\Channels\CookieChannel;
use Scaleum\Session\Contracts\SessionChannelInterface;
use Scaleum\Session\Contracts\SessionInterface;
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

    protected const EXPIRATION_DEFAULT          = 3600;
    protected array $data                       = [];
    protected int $ttl                          = self::EXPIRATION_DEFAULT;
    protected string $timeReference             = 'time';
    protected bool $logging                     = true;
    protected ?EventManagerInterface $events    = null;
    protected ?SessionChannelInterface $channel = null;
    protected ?string $id                       = null;
    private bool $isOpen                        = false;
    private bool $enable                        = true;
    ///////////////////////////////////////////////////////////////////////////
    abstract protected function read(): array;
    abstract protected function write(array $data): void;
    abstract protected function delete(): void;
    abstract public function cleanup(): void;
    ///////////////////////////////////////////////////////////////////////////
    public function ready(): void {

        $this->getEvents()->on(HandlerInterface::EVENT_GET_REQUEST, function (Event $event) {
            if (($request = $event->getParam('request')) && $request instanceof InboundRequest) {
                $this->id = $this->getChannel()?->fetchFromRequest($request) ?? null;
            }
            $this->open();
        }, -1);

        $this->getEvents()->on(HandlerInterface::EVENT_GET_RESPONSE, function (Event $event) {
            if (($response = $event->getParam('response')) && $response instanceof OutboundResponse) {
                if ($this->isOpen) {
                    $this->getChannel()?->writeToResponse($response, $this->id, $this->ttl);
                } else {
                    $this->getChannel()?->clearInResponse($response);
                }
            }
        }, -1);

        $this->getEvents()->on(KernelEvents::FINISH, function () {
            $this->update();
            $this->cleanup();
        }, 0);
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

    public function setEvents(EventManagerInterface $events): static
    {
        $this->events = $events;
        return $this;
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

    public function open(): bool {
        if (! $this->enable) {
            $this->debug('Session is disabled.');
            return false;
        }

        ! $this->logging || $this->debug('Session opening...');
        $this->isOpen = true;

        if ($isNotExists = ($id = $this->id) === null) {
            ! $this->logging || $this->debug('Session not found, opening a new one...');
            $id = UniqueHelper::getUniqueID(UniqueHelper::getUniquePrefix() . HttpHelper::getUserIP());
        }

        $this->id   = $id;
        $this->data = $this->read();

        // if session loaded, verify execute
        if (! $this->isValid()) {
            $this->update(true);
            return false;
        }

        ! $this->logging || $this->debug('Session has successfully loaded');

        // update if session is new
        if ($isNotExists === true) {
            $this->update();
        }

        return true;
    }

    public function close(): static
    {
        if (! $this->enable) {
            $this->debug('Session is disabled.');
            return $this;
        }

        $this->delete();

        $this->isOpen = false;
        ! $this->logging || $this->debug('Session has been closed');

        return $this;
    }

    protected function update(bool $flush = false): static {
        // if session is not opened or disabled - do nothing
        if (! $this->isOpen || ! $this->enable) {
            $this->debug('Session is not opened or disabled, update skipped.');
            return $this;
        }

        // flush all data
        if ($flush === true) {
            ! $this->logging || $this->debug('Session was flushed');
            $this->flush();
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

        ! $this->logging || $this->debug('Session has successfully updated');

        return $this;
    }

    protected function getTimestamp(int $shift = 0): int {
        $ref = strtolower($this->timeReference);
        if ($ref === 'gmt' || $ref === 'utc') {
            return (new \DateTimeImmutable('now', new \DateTimeZone('UTC')))->getTimestamp() + $shift;
        }
        return time() + $shift;
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

    public function set(int | string $var, mixed $value = null, bool $updateImmediately = false): static
    {
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

    public function remove(string $key, bool $updateImmediately = true): static
    {
        unset($this->data[$key]);
        if ($updateImmediately == true) {
            $this->update();
        }
        return $this;
    }

    public function removeByPrefix(string $prefix, bool $updateImmediately = false): static
    {
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

    public function flush(bool $updateImmediately = false): static
    {
        $this->data = [];

        if ($updateImmediately == true) {
            $this->update();
        }
        return $this;
    }

    public function getExpiration() {
        return $this->ttl;
    }

    public function setExpiration(int $expiration) {
        $this->ttl = $expiration > 0 ? $expiration : self::EXPIRATION_DEFAULT;
        return $this;
    }

    /**
     * Get the value of id
     */
    public function getId(): string {
        return $this->id;
    }

    public function getChannel(): ?SessionChannelInterface {
        return $this->channel;
    }

    public function setChannel(array | SessionChannelInterface $channel): static
    {
        if (is_array($channel)) {
            $channel = self::createInstance(['class' => CookieChannel::class, ...$channel]);
        }
        $this->channel = $channel;
        return $this;
    }

    public function isEnable(): bool {
        return $this->enable;
    }

    public function setEnable(bool $enable): static
    {
        $this->enable = $enable;
        return $this;
    }
}
/** End of SessionAbstract **/
