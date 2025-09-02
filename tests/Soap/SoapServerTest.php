<?php

namespace Bdf\Soap;

use Bdf\Soap\Server\Request;
use Bdf\Soap\Server\Response;
use PHPUnit\Framework\TestCase;

/**
 * @group Bdf
 * @group Bdf_Soap
 * @group Bdf_Soap_SoapServer
 */
class SoapServerTest extends TestCase
{
    /**
     * 
     */
    public function test_default_values()
    {
        $server = new SoapServer();

        $this->assertInstanceOf(Request::class, $server->getRequest());
        $this->assertInstanceOf(Response::class, $server->getResponse());
        
        $this->assertEquals('rpc-encoded', $server->getOption('document'));
        $this->assertEquals(SOAP_SINGLE_ELEMENT_ARRAYS, $server->getOption('features'));
        $this->assertEquals(SOAP_1_2, $server->getOption('soap_version'));
        $this->assertEquals(['features' => SOAP_SINGLE_ELEMENT_ARRAYS, 'soap_version' => SOAP_1_2], $server->getSoapOptions());
    }

    /**
     *
     */
    public function test_constructor()
    {
        $server = new SoapServer('http://acme.com?wsdl', ['soap_version' => SOAP_1_1]);

        $this->assertEquals('http://acme.com?wsdl', $server->getWsdl());
        $this->assertEquals(SOAP_1_1, $server->getOption('soap_version'));
    }

    /**
     *
     */
    public function test_set_get_wsdl()
    {
        $server = new SoapServer();
        $server->setWsdl('http://acme.com?wsdl');

        $this->assertEquals('http://acme.com?wsdl', $server->getWsdl());
    }
}
