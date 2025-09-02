<?php

use Bdf\JsonRpc;

/**
 * @group Bdf_JsonRpc
 * @group Bdf_JsonRpc_Server
 * @group Bdf_JsonRpc_Server_Request
 */
class Bdf_JsonRpc_Server_RequestTest extends PHPUnit\Framework\TestCase
{
    /**
     * @var type 
     */
    protected $request;

    /**
     * @var type 
     */
    protected $json = array(
        '1.0' => '{"method":"run","id":"2","params":["parameterTest"]}',
        '2.0' => '{"method":"run","id":"2","params":["parameterTest"],"jsonrpc":"2.0"}',
    );

    /**
     * @var type 
     */
    protected $expecteds = array(
        '1.0' => array(
            'id'        => 2,
            'method'    => 'run',
            'params'    => array('parameterTest'),
        ),
        '2.0' => array(
            'id'        => 2,
            'jsonrpc'   => '2.0',
            'method'    => 'run',
            'params'    => array('parameterTest'),
        ),
    );
    
    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();
        
        $this->request = new JsonRpc\Server\Request(' ');
    }
    
    /**
     * 
     */
    public function test_setgetId()
    {
        $this->request->setId(1);
        $this->assertEquals('1', $this->request->getId());
    }
    
    /**
     * 
     */
    public function test_setgetVersion()
    {
        $this->request->setVersion('2.0');
        $this->assertEquals('2.0', $this->request->getVersion());
        
        $this->request->setVersion('1.0');
        $this->assertEquals('1.0', $this->request->getVersion());
    }
    
    /**
     * 
     */
    public function test_setgetVersion_set_default_version()
    {
        $this->request->setVersion('test');
        $this->assertEquals('1.0', $this->request->getVersion());
    }
    
    /**
     * 
     */
    public function test_setgetMethod()
    {
        $this->request->setMethod('run');
        $this->assertEquals('run', $this->request->getMethod());
        $this->assertEquals(false, $this->request->isMethodError());
    }
    
    /**
     * 
     */
    public function test_setgetMethod_set_error_for_invalid_char()
    {
        $this->request->setMethod('ru$n');
        $this->assertEquals(null, $this->request->getMethod());
        $this->assertEquals(true, $this->request->isMethodError());
    }
    
    /**
     * 
     */
    public function test_parseRawBody_version_1_0()
    {
        $this->request = new JsonRpc\Server\Request($this->json['1.0']);
        
        $expected = $this->expecteds['1.0'];
        
        $this->callMethod($this->request, 'parseRawBody');
        
        $this->assertEquals($expected['id'], $this->request->getId());
        $this->assertEquals('1.0', $this->request->getVersion());
        $this->assertEquals($expected['method'], $this->request->getMethod());
        $this->assertEquals($expected['params'], $this->request->getParams());
    }
    
    /**
     * 
     */
    public function test_parseRawBody_version_2_0()
    {
        $this->request = new JsonRpc\Server\Request($this->json['2.0']);
        
        $expected = $this->expecteds['2.0'];
        
        $this->callMethod($this->request, 'parseRawBody');
        
        $this->assertEquals($expected['id'], $this->request->getId());
        $this->assertEquals($expected['jsonrpc'], $this->request->getVersion());
        $this->assertEquals($expected['method'], $this->request->getMethod());
        $this->assertEquals($expected['params'], $this->request->getParams());
    }
    
    /**
     * 
     */
    public function test_setOptions_version_1_0()
    {
        $this->request = new JsonRpc\Server\Request($this->json['2.0']);
        
        $expected = $this->expecteds['1.0'];
        
        $this->request->setOptions($expected);
        
        $this->assertEquals($expected['id'], $this->request->getId());
        $this->assertEquals('1.0', $this->request->getVersion());
        $this->assertEquals($expected['method'], $this->request->getMethod());
        $this->assertEquals($expected['params'], $this->request->getParams());
    }
    
    /**
     * 
     */
    public function test_setOptions_version_2_0()
    {
        $this->request = new JsonRpc\Server\Request($this->json['2.0']);
        
        $expected = $this->expecteds['2.0'];
        
        $this->request->setOptions($expected);
        
        $this->assertEquals($expected['id'], $this->request->getId());
        $this->assertEquals($expected['jsonrpc'], $this->request->getVersion());
        $this->assertEquals($expected['method'], $this->request->getMethod());
        $this->assertEquals($expected['params'], $this->request->getParams());
    }
    
    /**
     * 
     */
    public function test_validate_for_invalid_method()
    {
        $this->request->setMethod('ru$n');
        
        $this->expectException('Bdf\JsonRpc\Exception\InvalidArgumentException', 'Invalid Request', -32600);
        
        $this->request->validate();
    }
    
    /**
     * 
     */
    public function test_validate_method_not_set()
    {
        $this->expectException('Bdf\JsonRpc\Exception\InvalidArgumentException', 'Invalid Request', -32600);
        
        $this->request->validate();
    }
    
    /**
     * 
     */
    public function test_toString_call_toJson()
    {
        $request = $this->createPartialMock(get_class($this->request), array('toJson'));
        
        $request->expects($this->once())->method('toJson')->willReturn('');
        $request->__toString();
    }
    
    /**
     * 
     */
    public function test_to_json_version_2_0()
    {
        $this->request = new JsonRpc\Server\Request($this->json['2.0']);
        
        $this->request->prepare();
        
        $this->assertEquals($this->json['2.0'], $this->request->toJson());
    }
    
    /**
     * 
     */
    public function test_to_json_version_1_0()
    {
        $this->request = new JsonRpc\Server\Request($this->json['1.0']);
        
        $this->request->prepare();
        
        $this->assertEquals($this->json['1.0'], $this->request->toJson());
    }

    private function callMethod(object $object, string $method, mixed ...$args): mixed
    {
        $fn = fn () => $object->$method(...$args);
        $fn = $fn->bindTo(null, $object);

        return $fn();
    }
}
