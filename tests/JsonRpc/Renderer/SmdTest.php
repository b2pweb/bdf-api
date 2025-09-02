<?php

use Bdf\Api\Definition\SimpleTypeDefinition;
use Bdf\JsonRpc;
use Bdf\JsonRpc\Renderer\ServiceSmb;
use Bdf\JsonRpc\Renderer\Smd;

/**
 * @group Bdf_JsonRpc
 * @group Bdf_JsonRpc_Renderer
 * @group Bdf_JsonRpc_Renderer_Smd
 */
class Bdf_JsonRpc_Renderer_SmdTest extends PHPUnit\Framework\TestCase
{
    /**
     * @var Smd
     */
    protected $smd;
    
    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();
        
        $this->smd = new Smd();
    }
    
    /**
     * 
     */
    public function testDefaultValues()
    {
        $this->assertEquals(null, $this->smd->getId());
        $this->assertEquals(null, $this->smd->getTarget());
        $this->assertEquals(null, $this->smd->getDescription());
        $this->assertEquals('POST', $this->smd->getTransport());
        $this->assertEquals('application/json', $this->smd->getContentType());
        $this->assertEquals(Smd::ENV_JSONRPC_2, $this->smd->getEnvelope());
        $this->assertEquals(null, $this->smd->isDojoCompatible());
        $this->assertEquals(array(), $this->smd->getServices());
    }
    
    /**
     * 
     */
    public function testSetGetTransport()
    {
        $expected = 'POST';
        
        $this->smd->setTransport($expected);
        
        $this->assertEquals($expected, $this->smd->getTransport());
    }
    
    /**
     * 
     */
    public function testSetTransportShouldThrowException()
    {
        $transport = 'testTransport';
        
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Invalid transport '{$transport}' specified");

        $this->smd->setTransport($transport);
    }
    
    /**
     * 
     */
    public function testSetGetEnveloppe()
    {
        $this->smd->setEnvelope(Smd::ENV_JSONRPC_1);
        $this->assertEquals(Smd::ENV_JSONRPC_1, $this->smd->getEnvelope());
        
        $this->smd->setEnvelope(Smd::ENV_JSONRPC_2);
        $this->assertEquals(Smd::ENV_JSONRPC_2, $this->smd->getEnvelope());
    }
    
    /**
     * 
     */
    public function testSetEnveloppeShouldThrowException()
    {
        $envelope = 'testEnveloppe';
        
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Invalid envelope type '{$envelope}'");

        $this->smd->setEnvelope($envelope);
    }
    
    /**
     * 
     */
    public function testSetGetContentType()
    {
        $expected = 'application/json';
        
        $this->smd->setContentType($expected);
            
        $this->assertEquals($expected, $this->smd->getContentType());
    }
    
    /**
     * 
     */
    public function testSetContentTypeShouldThrowException()
    {
        $contentType = 'invalidChar%';
        
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Invalid content type '{$contentType}' specified");

        $this->smd->setContentType($contentType);
    }
    
    /**
     * 
     */
    public function testSetGetTarget()
    {
        $this->smd->setTarget(2);
        
        $this->assertEquals('2', $this->smd->getTarget());
    }
    
    /**
     * 
     */
    public function testSetGetId()
    {
        $this->smd->setId(2);
        
        $this->assertEquals('2', $this->smd->getId());
    }
    
    /**
     * 
     */
    public function testSetGetDescription()
    {
        $this->smd->setDescription(2);
        
        $this->assertEquals('2', $this->smd->getDescription());
    }
    
    /**
     * 
     */
    public function testDojoCompatibility()
    {
        $this->smd->setDojoCompatible(true);
        $this->assertTrue($this->smd->isDojoCompatible());
        
        $this->smd->setDojoCompatible(false);
        $this->assertFalse($this->smd->isDojoCompatible());
        
        $this->smd->setDojoCompatible(1);
        $this->assertTrue($this->smd->isDojoCompatible());
    }
    
    /**
     * 
     */
    public function testSetOptionsCallsSetters()
    {
        $data = array(
            'id'            => 'testId',
            'envelope'      => 'testEnvelope',
            'contentType'   => 'testContentType',
            'description'   => 'testDescription',
            'target'        => 'testTarget',
            'transport'     => 'testTransport',
        );
        
        $this->smd = $this->createPartialMock(Smd::class, array(
            'setId',
            'setEnvelope',
            'setContentType',
            'setDescription',
            'setTarget',
            'setTransport',
        ));
        
        $this->smd->expects($this->once())->method('setId')->with($data['id']);
        $this->smd->expects($this->once())->method('setEnvelope')->with($data['envelope']);
        $this->smd->expects($this->once())->method('setContentType')->with($data['contentType']);
        $this->smd->expects($this->once())->method('setDescription')->with($data['description']);
        $this->smd->expects($this->once())->method('setTarget')->with($data['target']);
        $this->smd->expects($this->once())->method('setTransport')->with($data['transport']);
        
        $this->assertEquals($this->smd, $this->smd->setOptions($data));
    }
    
    /**
     * 
     */
    public function testAddServicesShouldCallAddService()
    {
        $data = array(
            'test1',
            'test2',
        );
        
        $this->smd = $this->createPartialMock(Smd::class, array(
            'addService',
        ));
        
        $this->smd->expects($this->at(0))->method('addService')->with('test1');
        $this->smd->expects($this->at(1))->method('addService')->with('test2');
        
        $this->smd->addServices($data);
    }
    
    /**
     * 
     */
    public function testGetAddServices()
    {
        $this->smd->addServices(array(
            array('name' => 'test1'),
            array('name' => 'test2'),
        ));
        
        $expected1 = new ServiceSmb('test1');
        $expected2 = new ServiceSmb('test2');
        
        $this->assertEquals($expected1, $this->smd->getService('test1'));
        $this->assertEquals($expected2, $this->smd->getService('test2'));
        $this->assertEquals(array('test1' => $expected1, 'test2' => $expected2), $this->smd->getServices());
        
        $this->assertTrue($this->smd->removeService('test1'));
        $this->assertFalse($this->smd->removeService('test1'));
        $this->assertFalse($this->smd->getService('test1'));
        $this->assertEquals(array('test2' => $expected2), $this->smd->getServices());
        
        $this->smd->addService($expected1);
        $this->assertEquals($expected1, $this->smd->getService('test1'));
        $this->assertEquals(array('test1' => $expected1, 'test2' => $expected2), $this->smd->getServices());
    }
    
    /**
     * 
     */
    public function testSetServices()
    {
        $this->smd->addServices(array(
            array('name' => 'test1'),
            array('name' => 'test2'),
        ));
        
        $expected = new ServiceSmb('test3');
        
        $this->smd->setServices(array($expected));
        $this->assertEquals(array('test3' => $expected), $this->smd->getServices());
    }
    
    /**
     * 
     */
    public function testAddServiceWithServiceObject()
    {
        
        $expected = new ServiceSmb(array(
            'name'   => 'test1',
            'return' => new SimpleTypeDefinition('string'),
        ));
        
        $this->smd->addService($expected);
        $this->assertEquals($expected, $this->smd->getService('test1'));
    }
    
    /**
     * 
     */
    public function testAddServiceWithArray()
    {
        $this->smd->addService(array(
            'name'   => 'test1',
            'return' => new SimpleTypeDefinition('string'),
        ));
        
        $expected = new ServiceSmb(array(
            'name'   => 'test1',
            'return' => new SimpleTypeDefinition('string'),
        ));
        
        $this->assertEquals($expected, $this->smd->getService('test1'));
    }
    
    /**
     * 
     */
    public function testAddServiceThrowInvalidArgumentException()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid service passed to addService()');

        $this->smd->addService('test1');
    }
    
    /**
     * 
     */
    public function testAddServiceThrowAlreadyDefinedServiceException()
    {
        $data = new ServiceSmb('test3');
        $this->smd->addService($data);
        
        $this->expectException('Bdf\JsonRpc\Exception\RuntimeException');
        $this->expectExceptionMessage('Attempt to register a service already registered detected');

        $this->smd->addService($data);
    }
    
    /**
     * 
     */
    public function testToArrayWithDefaultValues()
    {
        $expected = array(
            'transport'     => $this->smd->getTransport(),
            'envelope'      => $this->smd->getEnvelope(),
            'contentType'   => $this->smd->getContentType(),
            'SMDVersion'    => Smd::SMD_VERSION
        );
        
        $this->assertEquals($expected, $this->smd->toArray());
    }
    
    /**
     * 
     */
    public function testToArrayWithAllValues()
    {
        $this->smd->setOptions(array(
            'target'        => 'TestTarget',
            'id'            => 'TestId',
        ));
        
        $service = new ServiceSmb(array(
            'name'   => 'test1',
            'return' => new SimpleTypeDefinition('string'),
        ));
        
        $expected = array(
            'transport'     => $this->smd->getTransport(),
            'envelope'      => $this->smd->getEnvelope(),
            'contentType'   => $this->smd->getContentType(),
            'SMDVersion'    => Smd::SMD_VERSION,
            'target'        => $this->smd->getTarget(),
            'id'            => $this->smd->getId(),
            'services'      => array(
                'test1' => array(
                    'envelope'   => $this->smd->getEnvelope(),
                    'transport'  => $this->smd->getTransport(),
                    'parameters' => array(),
                    'returns'    => 'string',
                )
            )
        );
        
        $this->smd->addService($service);
        
        $this->assertEquals($expected, $this->smd->toArray());
    }
    
    /**
     * 
     */
    public function testToArrayCallDojoCompatibility()
    {
        $expected = 'testDojo';
        
        $this->smd = $this->createPartialMock(Smd::class, array('toDojoArray'));
        $this->smd->setDojoCompatible(true);
        
        $this->smd->expects($this->once())->method('toDojoArray')->will($this->returnValue($expected));
        
        $this->assertEquals($expected, $this->smd->toArray());
    }
    
    /**
     * 
     */
    public function testToDojoArrayWithDefaultValues()
    {
        $expected = array(
            'SMDVersion'    => '.1',
            'serviceType'   => 'JSON-RPC',
        );
        
        $this->assertEquals($expected, $this->smd->toDojoArray());
    }
    
    /**
     * 
     */
    public function testToDojoArrayWithAllValues()
    {
        $this->smd->setOptions(array(
            'target' => 'TestTarget',
            'id'     => 'TestId',
        ));
        
        $service = new ServiceSmb(array(
            'name'   => 'test1',
            'return' => new SimpleTypeDefinition('string'),
        ));
        
        $expected = array(
            'SMDVersion'    => '.1',
            'serviceType'   => 'JSON-RPC',
            'methods'       => array(
                array(
                    'name'       => $service->getName(),
                    'serviceURL' => $this->smd->getTarget(),
                )
            )
        );
        
        $this->smd->addService($service);
        
        $this->assertEquals($expected, $this->smd->toDojoArray());
    }
    
    /**
     * 
     */
    public function testToJsonToString()
    {
        $this->smd->setOptions(array(
            'target'        => 'TestTarget',
            'id'            => 'TestId',
        ));
        
        $service = new ServiceSmb(array(
            'name'   => 'test1',
            'return' => new SimpleTypeDefinition('string'),
        ));
        
        $expected = json_encode(array(
            'transport'     => 'POST',
            'envelope'      => 'JSON-RPC-2.0',
            'contentType'   => 'application/json',
            'SMDVersion'    => '2.0',
            'target'        => 'TestTarget',
            'id'            => 'TestId',
            'services'      => array(
                'test1' => array(
                    'envelope'   => 'JSON-RPC-2.0',
                    'transport'  => 'POST',
                    'parameters' => array(),
                    'returns'    => 'string',
                )
            )
        ), JSON_PRETTY_PRINT);
        
        $this->smd->addService($service);
        
        $this->assertEquals($expected, $this->smd->toJson());
        $this->assertEquals($expected, '' . $this->smd);
    }
}
