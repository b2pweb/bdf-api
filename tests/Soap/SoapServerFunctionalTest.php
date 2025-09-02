<?php

namespace Bdf\Soap;

use Bdf\Fixtures\TestApplication;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

class SoapServerFunctionalTest extends TestCase
{
    private TestApplication $app;
    private array $proc = [];

    protected function setUp(): void
    {
        $this->app = new TestApplication();
        $this->app->boot();
    }

    protected function tearDown(): void
    {
        foreach ($this->proc as $pid) {
            @posix_kill($pid, SIGKILL);
        }

        $this->proc = [];
    }

    public function test_wsdl()
    {
        $response = $this->app->handle(Request::create('/api/soap/my_secret_key/myservice?wsdl', 'GET'));
        $expected = <<<'XML'
        <?xml version="1.0" encoding="UTF-8"?>
        <definitions 
            xmlns="http://schemas.xmlsoap.org/wsdl/"
            xmlns:wsdl="http://schemas.xmlsoap.org/wsdl/" 
            xmlns:tns="http://localhost/api/soap/my_secret_key/myservice" 
            xmlns:soap="http://schemas.xmlsoap.org/wsdl/soap/" 
            xmlns:xsd="http://www.w3.org/2001/XMLSchema" 
            xmlns:soap-enc="http://schemas.xmlsoap.org/soap/encoding/" 
            xmlns:soap12="http://schemas.xmlsoap.org/wsdl/soap12/" 
            name="MyService" 
            targetNamespace="http://localhost/api/soap/my_secret_key/myservice"
        >
            <types>
                <xsd:schema targetNamespace="http://localhost/api/soap/my_secret_key/myservice">
                    <xsd:complexType name="StringResponse">
                        <xsd:all>
                            <xsd:element name="result" type="xsd:string" nillable="true"/>
                            <xsd:element name="success" type="xsd:boolean" nillable="true"/>
                        </xsd:all>
                    </xsd:complexType>
                </xsd:schema>
            </types>
            <portType name="MyServicePort">
                <operation name="hello">
                    <input message="tns:helloIn"/>
                    <output message="tns:helloOut"/>
                </operation>
            </portType>
            <binding name="MyServiceBinding" type="tns:MyServicePort">
                <soap:binding style="rpc" transport="http://schemas.xmlsoap.org/soap/http"/>
                <operation name="hello">
                    <soap:operation soapAction="http://localhost/api/soap/my_secret_key/myservice#hello"/>
                    <input><soap:body use="encoded" encodingStyle="http://schemas.xmlsoap.org/soap/encoding/" namespace="http://localhost/api/soap/my_secret_key/myservice"/></input>
                    <output><soap:body use="encoded" encodingStyle="http://schemas.xmlsoap.org/soap/encoding/" namespace="http://localhost/api/soap/my_secret_key/myservice"/></output>
                </operation>
            </binding>
            <service name="MyServiceService">
                <port name="MyServicePort" binding="tns:MyServiceBinding">
                <soap:address location="http://localhost/api/soap/my_secret_key/myservice"/>
                </port>
            </service>
            <message name="helloIn"><part name="name" type="xsd:string"/></message>
            <message name="helloOut"><part name="return" type="tns:StringResponse"/></message>
        </definitions>
        XML;

        $this->assertXmlStringEqualsXmlString($expected, $response->getContent());
    }

    public function test_call()
    {
        $this->startServer();

        $client = new SoapClient('http://127.0.0.1:5000/api/soap/my_secret_key/myservice?wsdl');
        $result = $client->call('hello', ['name' => 'World']);

        $this->assertEquals((object) [
            'result' => 'Hello World',
            'success' => true,
        ], $result);
    }

    public function test_call_invalid_method()
    {
        $this->expectException(\SoapFault::class);
        $this->expectExceptionMessage('Function ("invalid") is not a valid method for this service');

        $this->startServer();

        $client = new SoapClient('http://127.0.0.1:5000/api/soap/my_secret_key/myservice?wsdl');
        $client->call('invalid');
    }

    public function test_call_error()
    {
        $this->expectException(\SoapFault::class);
        $this->expectExceptionMessage('invalid name');

        $this->startServer();

        $client = new SoapClient('http://127.0.0.1:5000/api/soap/my_secret_key/myservice?wsdl');
        $client->call('hello', ['name' => 'error']);
    }

    private function startServer(): void
    {
        $cmd = 'php -S 127.0.0.1:5000 ' . escapeshellarg(__DIR__ . '/../Fixtures/api.php') . ' >> '.escapeshellarg(__DIR__ . '/../../api.log').' 2>&1 & echo $!';
        $pid = (int) shell_exec($cmd);

        // Wait for server to be up
        $tries = 0;
        while ($tries++ < 50) {
            $fp = @fsockopen('127.0.0.1', 5000, $errno, $errstr, 0.1);
            if ($fp) {
                fclose($fp);
                break;
            }
            usleep(100000);

        }

        $this->proc[] = $pid;
    }
}
