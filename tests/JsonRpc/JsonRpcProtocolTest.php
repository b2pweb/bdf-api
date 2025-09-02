<?php

namespace Bdf\JsonRpc;

use Bdf\Api\Api;
use Bdf\Api\ApiRequest;
use Bdf\Api\Definition\Builder\ServiceDefinitionBuilder;
use DI\Container;

/**
 * @group Bdf_JsonRpc
 */
class JsonRpcProtocolTest extends \PHPUnit\Framework\TestCase
{
    /**
     * 
     */
    public function test_is_service_map()
    {
        $protocol = new JsonRpcProtocol(new Container());

        $request = ApiRequest::create('/api');
        $this->assertTrue($protocol->isServiceMapRequest($request));

        $request = ApiRequest::create('/api', 'POST');
        $this->assertFalse($protocol->isServiceMapRequest($request));
    }

    /**
     *
     */
    public function test_handle_service_map()
    {
        $protocol = new JsonRpcProtocol(new Container());

        $request = ApiRequest::create('/api');
        $request->attributes->set('_api', $this->createApi('foo', function() {

        }));

        $response = [
            "transport" => "POST",
            "envelope" => "JSON-RPC-2.0",
            "contentType" => "application/json",
            "SMDVersion" => "2.0",
            "target" => "http://localhost/api",
        ];

        $this->assertEquals(json_encode($response, JSON_PRETTY_PRINT), $protocol->handle($request)->getContent());
    }

    /**
     * @param string $name
     * @param \Closure $builder
     *
     * @return Api
     */
    private function createApi($name, $callback)
    {
        $builder = new ServiceDefinitionBuilder();
        $callback($builder);

        $api = new Api();
        $api->setName($name);
        $api->setDefinition($builder->build());
        $api->setServiceClass(FooService::class);

        return $api;
    }
}

//-------------
class FooService
{

}
