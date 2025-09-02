<?php

namespace Bdf\Soap;

use Bdf\Soap\Driver\DriverInterface;
use PHPUnit\Framework\TestCase;

/**
 * @group Bdf
 * @group Bdf_Soap
 * @group Bdf_Soap_SoapClient
 */
class SoapClientTest extends TestCase
{
    /**
     *
     */
    public function test_default_constructor()
    {
        $client = new SoapClient();

        $soapOptions = [
            'soap_version'      => \SOAP_1_2,
            'features'          => \SOAP_SINGLE_ELEMENT_ARRAYS,
            'trace'             => true,
            'keep_alive'        => false,
        ];

        $this->assertNull($client->getWsdl());
        $this->assertSame([], $client->getOptions());
        $this->assertSame($soapOptions, $client->getSoapOptions());
    }

    /**
     *
     */
    public function test_constructor_wsdl()
    {
        $client = new SoapClient('/foo?wsdl');

        $this->assertEquals('/foo?wsdl', $client->getWsdl());
    }

    /**
     * 
     */
    public function test_set_get_wsdl()
    {
        $client = new SoapClient();
        $client->setWsdl('/foo?wsdl');

        $this->assertEquals('/foo?wsdl', $client->getWsdl());
    }

    /**
     *
     */
    public function test_set_get_options()
    {
        $client = new SoapClient();
        $client->setOptions(['class' => 'Foo']);

        $this->assertSame(['class' => 'Foo'], $client->getOptions());
        $this->assertSame('Foo', $client->getOption('class'));
    }

    /**
     *
     */
    public function test_set_get_driver()
    {
        $client = new SoapClient();
        $client->setDriver('Foo');

        $this->assertSame('Foo', $client->getDriver());
    }

    /**
     *
     */
    public function test_set_get_soap_client()
    {
        $driver = $this->createMock(DriverInterface::class);
        $client = new SoapClient();
        $client->setSoapClient($driver);

        $this->assertSame($driver, $client->getSoapClient());
    }

    /**
     *
     */
    public function test_get_last_request()
    {
        $driver = $this->createMock(DriverInterface::class);
        $driver->expects($this->once())->method('__getLastRequest')->willReturn('bar');
        $client = new SoapClient();

        $this->assertSame('', $client->getLastRequest());

        $client->setSoapClient($driver);
        $this->assertSame('bar', $client->getLastRequest());
    }

    /**
     *
     */
    public function test_get_last_response()
    {
        $driver = $this->createMock(DriverInterface::class);
        $driver->expects($this->once())->method('__getLastResponse')->willReturn('bar');
        $client = new SoapClient();

        $this->assertSame('', $client->getLastResponse());

        $client->setSoapClient($driver);
        $this->assertSame('bar', $client->getLastResponse());
    }

    /**
     *
     */
    public function test_get_last_request_header()
    {
        $driver = $this->createMock(DriverInterface::class);
        $driver->expects($this->once())->method('__getLastRequestHeaders')->willReturn('bar');
        $client = new SoapClient();

        $this->assertSame('', $client->getLastRequestHeaders());

        $client->setSoapClient($driver);
        $this->assertSame('bar', $client->getLastRequestHeaders());
    }

    /**
     *
     */
    public function test_get_last_response_header()
    {
        $driver = $this->createMock(DriverInterface::class);
        $driver->expects($this->once())->method('__getLastResponseHeaders')->willReturn('bar');
        $client = new SoapClient();

        $this->assertSame('', $client->getLastResponseHeaders());

        $client->setSoapClient($driver);
        $this->assertSame('bar', $client->getLastResponseHeaders());
    }

    /**
     *
     */
    public function test_get_functions()
    {
        $driver = $this->createMock(DriverInterface::class);
        $driver->expects($this->once())->method('__getFunctions')->willReturn(['bar']);
        $client = new SoapClient();
        $client->setSoapClient($driver);

        $this->assertSame(['bar'], $client->getFunctions());
    }

    /**
     *
     */
    public function test_get_types()
    {
        $driver = $this->createMock(DriverInterface::class);
        $driver->expects($this->once())->method('__getTypes')->willReturn(['bar']);
        $client = new SoapClient();
        $client->setSoapClient($driver);

        $this->assertSame(['bar'], $client->getTypes());
    }

    /**
     *
     */
    public function test_last_method()
    {
        $driver = $this->createMock(DriverInterface::class);
        $driver->expects($this->once())->method('preProcessArguments')->willReturn([]);

        $client = new SoapClient();
        $client->setSoapClient($driver);

        $this->assertSame(null, $client->getLastMethod());

        $client->call('foo');

        $this->assertSame('foo', $client->getLastMethod());
    }
}
