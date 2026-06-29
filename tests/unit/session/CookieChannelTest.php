<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Scaleum\Http\InboundRequest;
use Scaleum\Http\OutboundResponse;
use Scaleum\Http\Uri;
use Scaleum\Session\Channels\CompositeChannel;
use Scaleum\Session\Channels\CookieChannel;

class CookieChannelTest extends TestCase
{
    public function testFetchFromRequestReturnsCookieValue(): void
    {
        $request = new InboundRequest('GET', new Uri('/'), cookieParams: ['SID' => 'session-id']);
        $channel = new CookieChannel();

        $this->assertSame('session-id', $channel->fetchFromRequest($request));
    }

    public function testFetchFromRequestReturnsEncodedCookieValue(): void
    {
        $response = new OutboundResponse();
        $channel = (new CookieChannel())->setEncode(true);
        $channel->writeToResponse($response, 'session-id');

        $cookie = $this->extractCookieValue($response->getHeader('Set-Cookie')[0]);
        $request = new InboundRequest('GET', new Uri('/'), cookieParams: ['SID' => $cookie]);

        $this->assertSame('session-id', $channel->fetchFromRequest($request));
    }

    public function testFetchFromRequestRejectsTamperedEncodedCookieValue(): void
    {
        $response = new OutboundResponse();
        $channel = (new CookieChannel())->setEncode(true);
        $channel->writeToResponse($response, 'session-id');

        $cookie = $this->extractCookieValue($response->getHeader('Set-Cookie')[0]);
        $tamperedCookie = ($cookie[0] === 'a' ? 'b' : 'a') . substr($cookie, 1);
        $request = new InboundRequest('GET', new Uri('/'), cookieParams: ['SID' => $tamperedCookie]);

        $this->assertNull($channel->fetchFromRequest($request));
    }

    public function testWriteToResponseWritesCookieWithTtlAttributes(): void
    {
        $response = new OutboundResponse();
        $channel = (new CookieChannel())
            ->setPath('/admin')
            ->setDomain('example.test')
            ->setSecure(true)
            ->setHttpOnly(true)
            ->setSameSite('Strict');

        $channel->writeToResponse($response, 'session-id', 120);

        $cookies = $response->getHeader('Set-Cookie');

        $this->assertCount(1, $cookies);
        $this->assertStringStartsWith('SID=session-id; Expires=', $cookies[0]);
        $this->assertStringContainsString('; Max-Age=120; Path=/admin; Domain=example.test; Secure; HttpOnly; SameSite=Strict', $cookies[0]);
    }

    public function testWriteToResponseDeduplicatesCookieForSameScope(): void
    {
        $response = new OutboundResponse();
        $channel = new CookieChannel();

        $channel->writeToResponse($response, 'first');
        $channel->writeToResponse($response, 'second');

        $cookies = $response->getHeader('Set-Cookie');

        $this->assertCount(1, $cookies);
        $this->assertSame('SID=second; Path=/; SameSite=Lax', $cookies[0]);
    }

    public function testClearInResponseUsesConfiguredCookieScope(): void
    {
        $response = new OutboundResponse();
        $channel = (new CookieChannel())
            ->setPath('/admin')
            ->setDomain('example.test')
            ->setSecure(true)
            ->setHttpOnly(true)
            ->setSameSite('Strict');

        $channel->clearInResponse($response);

        $cookie = $response->getHeader('Set-Cookie')[0];

        $this->assertStringStartsWith('SID=deleted; Expires=', $cookie);
        $this->assertStringContainsString('; Max-Age=0; Path=/admin; Domain=example.test; Secure; HttpOnly; SameSite=Strict', $cookie);
    }

    public function testSecretAliasesCookieSalt(): void
    {
        $channel = (new CookieChannel())->setSecret('custom-secret');

        $this->assertSame('custom-secret', $channel->getSecret());
        $this->assertSame('custom-secret', $channel->getSalt());
    }

    public function testSameSiteNoneEnablesSecureFlag(): void
    {
        $channel = (new CookieChannel())->setSameSite('None');

        $this->assertTrue($channel->isSecure());
    }

    public function testCompositeChannelAddsHeaderTransport(): void
    {
        $response = new OutboundResponse();
        $channel = new CompositeChannel();

        $channel->writeToResponse($response, 'session-id');

        $this->assertSame(['session-id'], $response->getHeader('X-SID'));
        $this->assertSame(['SID=session-id; Path=/; SameSite=Lax'], $response->getHeader('Set-Cookie'));
    }

    private function extractCookieValue(string $cookie): string
    {
        $firstSegment = explode(';', $cookie, 2)[0];

        return explode('=', $firstSegment, 2)[1] ?? '';
    }
}
