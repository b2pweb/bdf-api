<?php

namespace Bdf\JsonRpc;

use Bdf\JsonRpc\Server\Error;
use Bdf\JsonRpc\Server\Request;
use Bdf\JsonRpc\Server\Response;

/**
 * @group Bdf_JsonRpc
 * @group Bdf_JsonRpc_Server
 */
class JsonRpcServerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var JsonRpcServer
     */
    protected $server;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();
        
        $this->server = new JsonRpcServer();
    }
    
    /**
     * 
     */
    public function test_server_request()
    {
        $this->assertInstanceOf(Request::class, $this->server->getRequest());
        $this->assertInstanceOf(Response::class, $this->server->getResponse());
    }
    
    /**
     * 
     */
    public function test_instanciateFault()
    {
        $fault = $this->callMethod($this->server, 'instanciateFault', 'test');
        
        $this->assertInstanceOf(Error::class, $fault);
        $this->assertEquals('test', $fault->getMessage());
        $this->assertEquals(-32000, $fault->getCode());
    }

    private function callMethod(object $object, string $method, mixed ...$args): mixed
    {
        $fn = fn () => $object->$method(...$args);
        $fn = $fn->bindTo(null, $object);

        return $fn();
    }
}
