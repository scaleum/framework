<?php
declare (strict_types = 1);
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\StreamInterface;
use Scaleum\Http\ClientRequest;
use Scaleum\Http\ClientResponse;
use Scaleum\Http\Client\Transport\CurlTransport;
use Scaleum\Http\InboundResponse;
use Scaleum\Http\OutboundRequest;
use Scaleum\Http\Stream;
use Scaleum\Http\Uri;
use Scaleum\Stdlib\Exceptions\EHttpException;
use Scaleum\Stdlib\Exceptions\ERuntimeError;

class CurlTransportTest extends TestCase {
    public function testGetAndSetAuthType() {
        $transport = new CurlTransport();
        $transport->setAuthType('BASIC');
        $this->assertEquals('BASIC', $transport->getAuthType());
    }

    public function testGetAndSetPassword() {
        $transport = new CurlTransport();
        $transport->setPassword('password');
        $this->assertEquals('password', $transport->getPassword());
    }

    public function testGetAndSetUsername() {
        $transport = new CurlTransport();
        $transport->setUsername('username');
        $this->assertEquals('username', $transport->getUsername());
    }

    public function testGetAndSetToken() {
        $transport = new CurlTransport();
        $transport->setToken('token');
        $this->assertEquals('token', $transport->getToken());
    }

    public function testGetAndSetDomain() {
        $transport = new CurlTransport();
        $transport->setDomain('domain');
        $this->assertEquals('domain', $transport->getDomain());
    }

    public function testIsSupported() {
        $transport = new CurlTransport();
        $this->assertTrue($transport->isSupported());
    }

    public function testSendThrowsExceptionWhenCurlIsNotSupported() {
        $this->expectException(ERuntimeError::class);
        /**
         * @var MockBuilder&CurlTransport
         */
        $transport = $this->getMockBuilder(CurlTransport::class)
            ->onlyMethods(['isSupported'])
            ->getMock();
        $transport->method('isSupported')->willReturn(false);

        /**
         * @var MockObject&ClientRequest
         */
        $request = $this->createMock(OutboundRequest::class);
        $transport->send($request);
    }

    public function _testSendThrowsExceptionForMalformedUrl() {
        $this->expectException(EHttpException::class);
        $transport = new CurlTransport();
        /**
         * @var MockObject&ClientRequest
         */
        $request = $this->createMock(OutboundRequest::class);
        $request->method('getUri')->willReturn(new Uri('malformed-url'));

        $transport->send($request);
    }

    public function _testSendReturnsClientResponse() {
        /**
         * @var MockObject&CurlTransport
         */
        $transport = $this->getMockBuilder(CurlTransport::class)
            ->onlyMethods(['isSupported'])
            ->getMock();
        $transport->method('isSupported')->willReturn(true);

        $request = $this->createMock(OutboundRequest::class);
        $request->method('getUri')->willReturn(new Uri('http://example.com'));
        $request->method('getMethod')->willReturn('GET');
        $request->method('getHeaders')->willReturn([]);
        $request->method('isAsync')->willReturn(false);
        $request->method('getProtocolVersion')->willReturn('1.1');

        $response = $transport->send($request);
        $this->assertInstanceOf(InboundResponse::class, $response);
    }

    public function testSendRequestToLocal() {
        $transport = new CurlTransport([
            'timeout' => 5,
            'redirectsCount' => 10
        ]);

        $request = new OutboundRequest(
            uri: new Uri('http://localhost:8080/api/request/1'),
            method: 'GET',
            body: new Stream(fopen('php://temp', 'w+'))
        );
        $response = $transport->send($request);
        var_export($response->getBody()->getContents()); 
        var_export($response->getHeaders());         
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testSendArray() {
        $transport = new CurlTransport([
            'timeout' => 5,
            'redirectsCount' => 10
        ]);

        $arrayData = new Stream(fopen('php://temp', 'w+b'));
        $arrayData->write(json_encode(['test' => 'test2222']));
        $arrayData->rewind();

        $request = new OutboundRequest(
            uri: new Uri('http://localhost:8080/api/request-array'),
            method: 'POST',
            body: ['test' => 'test3333']
        );
        $response = $transport->send($request);
        var_export($response->getParsedBody()); 
        var_export($response->getHeaders());   
        $this->assertEquals(200, $response->getStatusCode());
    }
}