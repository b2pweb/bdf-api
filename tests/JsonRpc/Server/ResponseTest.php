<?php

use Bdf\JsonRpc;

/**
 * @group Bdf_JsonRpc
 * @group Bdf_JsonRpc_Server
 * @group Bdf_JsonRpc_Server_Response
 */
class Bdf_JsonRpc_Server_ResponseTest extends PHPUnit\Framework\TestCase
{
    /**
     * @var type 
     */
    protected $response;

    /**
     * @var type 
     */
    protected $json = array(
        '1.0' => '{"result":"Hello JSON-RPC","id":3,"error":null}',
        '2.0' => '{"result":"Hello JSON-RPC","id":3,"jsonrpc":"2.0"}',
    );

    /**
     * @var type 
     */
    protected $jsonError = array(
        '1.0' => '{"error":{"code":-32000,"message":"test","data":null},"id":3,"result":null}',
        '2.0' => '{"error":{"code":-32000,"message":"test","data":null},"id":3,"jsonrpc":"2.0"}',
    );

    
    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();
        
        $this->response = new JsonRpc\Server\Response();
    }
    
    /**
     * 
     */
    public function test_setgetId()
    {
        $this->response->setId(1);
        $this->assertEquals('1', $this->response->getId());
    }
    
    /**
     * 
     */
    public function test_setgetVersion()
    {
        $this->response->setVersion('2.0');
        $this->assertEquals('2.0', $this->response->getVersion());
        
        $this->response->setVersion('1.0');
        $this->assertEquals(null, $this->response->getVersion());
    }
    
    /**
     * 
     */
    public function test_setgetVersion_set_array_version()
    {
        $this->response->setVersion(array('2.0'));
        $this->assertEquals('2.0', $this->response->getVersion());
    }
    
    /**
     * 
     */
    public function test_setgetError()
    {
        $this->assertEquals(false, $this->response->isError());
        
        $this->response->setError(new \Exception('test'));
        
        $this->assertEquals(true, $this->response->isError());
        $this->assertInstanceof('Bdf\JsonRpc\Server\Error', $this->response->getError());
        $this->assertEquals('test', $this->response->getError()->getMessage());
        
    }
    
    /**
     * 
     */
    public function test_setgetError_with_error()
    {
        $error = new JsonRpc\Server\Error(new \Exception('test'));
        
        $this->response->setError($error);
        
        $this->assertEquals($error, $this->response->getError());
    }
    
    /**
     * 
     */
    public function test_getHeader_with_no_id()
    {
        $this->assertEquals(array('HTTP/1.1 204 No Content'), $this->response->getHeader());
    }
    
    /**
     * 
     */
    public function test_getHeader()
    {
        $this->response->setId('2');
            
        $this->assertEquals(array('Content-Type' => 'application/json'), $this->response->getHeader());
    }
    
    /**
     * 
     */
    public function test_getHeader_with_error()
    {
        $this->response->setError(new \Exception('test'));
        
        $this->assertEquals(array('Content-Type' => 'application/json'), $this->response->getHeader());
    }
    
    /**
     * 
     */
    public function test_toJson_with_no_id()
    {
        $this->assertEquals('', $this->response->toJson());
    }
    
    /**
     * 
     */
    public function test_toJson_version_1_0()
    {
        $this->response->setId(3);
        $this->response->setVersion('1.0');
        $this->response->setResult('Hello JSON-RPC');
        
        $this->assertEquals($this->json['1.0'], $this->response->toJson());
    }
    
    /**
     * 
     */
    public function test_toJson_version_2_0()
    {
        $this->response->setId(3);
        $this->response->setVersion('2.0');
        $this->response->setResult('Hello JSON-RPC');
        
        $this->assertEquals($this->json['2.0'], $this->response->toJson());
    }
    
    /**
     * 
     */
    public function test_toJson_error_version_1_0()
    {
        $this->response->setId(3);
        $this->response->setVersion('1.0');
        $this->response->setResult('Hello JSON-RPC');
        $this->response->setError(new \Exception('test'));
        
        $this->assertEquals($this->jsonError['1.0'], $this->response->toJson());
    }
    
    /**
     * 
     */
    public function test_toJson_error_version_2_0()
    {
        $this->response->setId(3);
        $this->response->setVersion('2.0');
        $this->response->setResult('Hello JSON-RPC');
        $this->response->setError(new \Exception('test'));
        
        $this->assertEquals($this->jsonError['2.0'], $this->response->toJson());
    }
}
