<?php

use Bdf\JsonRpc;

/**
 * @author Seb
 * @group Bdf_JsonRpc
 * @group Bdf_JsonRpc_Server
 * @group Bdf_JsonRpc_Server_Error
 */
class Bdf_JsonRpc_Server_ErrorTest extends PHPUnit\Framework\TestCase
{
    /**
     * @var type 
     */
    protected $error;
    
    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();
        
        $this->error = new JsonRpc\Server\Error();
    }
    
    /**
     * 
     */
    public function test_setgetCode()
    {
        $this->assertEquals($this->error, $this->error->setCode('testResult'));
        $this->assertEquals(-32000, $this->error->getCode());
        
        $this->assertEquals($this->error, $this->error->setCode(Bdf\JsonRpc\Server\Error::ERROR_INTERNAL));
        $this->assertEquals(Bdf\JsonRpc\Server\Error::ERROR_INTERNAL, $this->error->getCode());
        
        $this->assertEquals($this->error, $this->error->setCode(-32099));
        $this->assertEquals(-32099, $this->error->getCode());
        
        $this->assertEquals($this->error, $this->error->setCode(-32100));
        $this->assertEquals(-32099, $this->error->getCode());
        
        $this->assertEquals($this->error, $this->error->setCode(-32050));
        $this->assertEquals(-32050, $this->error->getCode());
        
        $this->assertEquals($this->error, $this->error->setCode(-32000));
        $this->assertEquals(-32000, $this->error->getCode());
        
        $this->assertEquals($this->error, $this->error->setCode(-31999));
        $this->assertEquals(-32000, $this->error->getCode());
    }
    
    /**
     * 
     */
    public function test_setgetData()
    {
        $this->assertEquals($this->error, $this->error->setData('testResult'));
        $this->assertEquals('testResult', $this->error->getData());
        
    }

    /**
     * 
     */
    public function test_toArray()
    {
        $this->error = new Bdf\JsonRpc\Server\Error('Test', Bdf\JsonRpc\Server\Error::ERROR_OTHER, 'test');
        $this->assertEquals(array(
            'code'      => Bdf\JsonRpc\Server\Error::ERROR_OTHER,
            'message'   => 'Test',
            'data'      => 'test',
        ), $this->error->toArray());
    }

    /**
     * 
     */
    public function test_toJson()
    {
        $expected = '{"code":' . Bdf\JsonRpc\Server\Error::ERROR_OTHER . ',"message":"Test","data":"test"}';
        
        $this->error = new Bdf\JsonRpc\Server\Error('Test', Bdf\JsonRpc\Server\Error::ERROR_OTHER, 'test');
        $this->assertEquals($expected, $this->error->toJson());
        
        ob_start();
        echo $this->error;
        $stdout = ob_get_contents();
        ob_end_clean();
        
        $this->assertEquals($expected, $stdout);
    }
}
