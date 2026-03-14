<?php

use PHPUnit\Framework\TestCase;
use Scaleum\Stdlib\Helpers\UrlHelper;

class UrlHelperTest extends TestCase
{
    protected function setUp(): void
    {
        $_SERVER = [];
    }

    public function testBaseUrlWithRelativeUrl()
    {
        $_SERVER['HTTP_HOST'] = 'localhost';
        $_SERVER['HTTPS'] = 'off';
        $_SERVER['SERVER_PORT'] = 80;

        $this->assertEquals('http://localhost:80/path', UrlHelper::baseUrl('path'));
    }

    public function testBaseUrlWithEmptyString()
    {
        $_SERVER['HTTP_HOST'] = 'localhost';
        $_SERVER['HTTPS'] = 'off';
        $_SERVER['SERVER_PORT'] = 80;

        $this->assertEquals('http://localhost:80/', UrlHelper::baseUrl());
    }

    public function testGetServerName()
    {
        $_SERVER['HTTP_HOST'] = 'example.com';
        $this->assertEquals('example.com', UrlHelper::getServerName());
    }

    public function testGetServerProtocolHttp()
    {
        $_SERVER['HTTPS'] = 'off';
        $_SERVER['SERVER_PORT'] = 80;
        $this->assertEquals('http', UrlHelper::getServerProtocol());
    }

    public function testGetServerProtocolHttps()
    {
        $_SERVER['HTTPS'] = 'on';
        $_SERVER['SERVER_PORT'] = 443;
        $this->assertEquals('https', UrlHelper::getServerProtocol());
    }

    public function testParseWithFullUrl()
    {
        $url = 'http://example.com:8080/path/file?query=string#hash';
        $expected = [            
            'protocol' => 'http',
            'hostname' => 'example.com',
            'port' => '8080',
            'path' => '/path/',
            'file' => 'file',
            'query' => 'query=string',
            'hash' => '#hash'
        ];
        $result = UrlHelper::parse($url);

        $this->assertArrayHasKey('protocol', $result);
        $this->assertEquals($expected['protocol'], $result['protocol']);
        $this->assertArrayHasKey('hostname', $result);
        $this->assertEquals($expected['hostname'], $result['hostname']);
        $this->assertArrayHasKey('port', $result);
        $this->assertEquals($expected['port'], $result['port']);
        $this->assertArrayHasKey('path', $result);
        $this->assertEquals($expected['path'], $result['path']);
        $this->assertArrayHasKey('file', $result);
        $this->assertEquals($expected['file'], $result['file']);
        $this->assertArrayHasKey('query', $result);
        $this->assertEquals($expected['query'], $result['query']);
        $this->assertArrayHasKey('hash', $result);
        $this->assertEquals($expected['hash'], $result['hash']);
    }

    public function testParseWithPartialUrl()
    {
        $url = 'http://example.com/path';
        $expected = [
            'protocol' => 'http',
            'hostname' => 'example.com',
            'port' => null,
            'path' => '/path'
        ];
        $result = UrlHelper::parse($url);

        $this->assertArrayHasKey('protocol', $result);
        $this->assertEquals($expected['protocol'], $result['protocol']);
        $this->assertArrayHasKey('hostname', $result);
        $this->assertEquals($expected['hostname'], $result['hostname']);
        $this->assertArrayHasKey('port', $result);
        $this->assertEquals($expected['port'], $result['port']);
        $this->assertArrayHasKey('path', $result);
        $this->assertEquals($expected['path'], $result['path']);      
    }
}