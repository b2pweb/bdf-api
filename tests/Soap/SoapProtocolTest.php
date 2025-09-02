<?php

namespace Bdf\Soap;

use Bdf\Api\ApiRequest;
use Bdf\Soap\Renderer\WsdlRenderer;
use DI\Container;
use PHPUnit\Framework\TestCase;

/**
 * @group Bdf_Soap
 */
class SoapProtocolTest extends TestCase
{
    /**
     * 
     */
    public function test_is_service_map()
    {
        $protocol = new SoapProtocol(new Container());

        $request = ApiRequest::create('/api?wsdl');
        $this->assertTrue($protocol->isServiceMapRequest($request));

        $request = ApiRequest::create('/api');
        $this->assertFalse($protocol->isServiceMapRequest($request));
    }

    /**
     *
     */
    public function test_server()
    {
        $protocol = new SoapProtocol(new Container());
        $request = ApiRequest::create('/api');

        $server = $this->callMethod($protocol, 'createServer', $request);

        $this->assertInstanceOf(SoapServer::class, $server);
    }

    /**
     *
     */
    public function test_createRenderer()
    {
        $protocol = new SoapProtocol(new Container());
        $request = ApiRequest::create('/api');

        $renderer = $this->callMethod($protocol, 'createRenderer', $request);

        $this->assertInstanceOf(WsdlRenderer::class, $renderer);
    }

    /**
     *
     */
    public function test_createRenderer_with_flags()
    {
        $protocol = new SoapProtocol(new Container());
        $request = ApiRequest::create('/api');
        $request->attributes->set('options', 'minOccurs:-nillable');

        $renderer = $this->callMethod($protocol, 'createRenderer', $request);

        $exceptedRenderer = new WsdlRenderer();
        $exceptedRenderer->setFlags(['minOccurs' => true, 'nillable' => false]);

        $this->assertEquals($exceptedRenderer, $renderer);
    }

    private function callMethod(object $object, string $method, mixed ...$args): mixed
    {
        $fn = fn () => $object->$method(...$args);
        $fn = $fn->bindTo(null, $object);

        return $fn();
    }
}
