<?php

namespace Bdf\Api\Mapping;

use Bdf\Api\Definition\MethodDefinition;
use DI\Container;
use PHPUnit\Framework\TestCase;

/**
 * Class MapperTest
 *
 * @package Bdf\Api\Mapping
 *
 * @group Bdf
 * @group Bdf_Api
 * @group Bdf_Api_Mapping
 * @group Bdf_Api_Mapping_Mapper
 *
 * @coversDefaultClass Bdf\Api\Mapping\Mapper
 */
class MapperTest extends TestCase
{
    /**
     *
     */
    public function test_mapMethod()
    {
        $methodMetadata = $this->getMockBuilder('Bdf\Api\Mapping\Metadata\MethodMetadata')
            ->disableOriginalConstructor()
            ->getMock();
        $methodMetadata->expects($this->once())->method('map')
            ->with([], new Context(new Container()))
            ->will($this->returnValue([5]));

        $serviceMetadata = $this->getMockBuilder('Bdf\Api\Mapping\Metadata\ServiceMetadata')
            ->disableOriginalConstructor()
            ->getMock();
        $serviceMetadata->expects($this->once())->method('getMethod')
            ->with('test')
            ->will($this->returnValue($methodMetadata));

        $this->assertEquals([5], (new Mapper(new Container(), $serviceMetadata))->mapMethod(new MethodDefinition('test'), []));
    }

    /**
     *
     */
    public function test_mapMethodReturn()
    {
        $methodMetadata = $this->getMockBuilder('Bdf\Api\Mapping\Metadata\MethodMetadata')
            ->disableOriginalConstructor()
            ->getMock();
        $methodMetadata->expects($this->once())->method('unmap')
            ->with(5, new Context(new Container()))
            ->will($this->returnValue(10));

        $serviceMetadata = $this->getMockBuilder('Bdf\Api\Mapping\Metadata\ServiceMetadata')
            ->disableOriginalConstructor()
            ->getMock();
        $serviceMetadata->expects($this->once())->method('getMethod')
            ->with('test')
            ->will($this->returnValue($methodMetadata));

        $mapper = new Mapper(new Container(), $serviceMetadata);

        $refl = (new \ReflectionProperty($mapper, 'context'));

        PHP_VERSION_ID >= 80100 or $refl->setAccessible(true);
        $refl->setValue($mapper, new Context(new Container()));

        $this->assertEquals(10, $mapper->mapMethodReturn(new MethodDefinition('test'), 5));
    }
}
