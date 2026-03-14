<?php

use PHPUnit\Framework\TestCase;
use Scaleum\Stdlib\Helper\XmlHelper;

class XmlHelperTest extends TestCase
{
    public function testIsXmlWithValidXml()
    {
        $validXml = '<root><child>content</child></root>';
        $this->assertTrue(XmlHelper::isXml($validXml));
    }

    public function testIsXmlWithInvalidXml()
    {
        $invalidXml = '<root><child>content</root>';
        $this->assertFalse(XmlHelper::isXml($invalidXml));
    }

    public function testIsXmlWithEmptyString()
    {
        $emptyString = '';
        $this->assertFalse(XmlHelper::isXml($emptyString));
    }

    public function testIsXmlWithNonXmlString()
    {
        $nonXmlString = 'Just a regular string';
        $this->assertFalse(XmlHelper::isXml($nonXmlString));
    }
}