<?php

namespace Bdf\Api\Mapping\Metadata;

use Bdf\Api\Mapping\Metadata\Types\TypeMetadataInterface;
use Bdf\Api\Mapping\MethodContext;
use PHPUnit\Framework\TestCase;

/**
 * Class ParameterMetadataTest
 *
 * @package Bdf\Api\Mapping\Metadata
 *
 * @group Bdf
 * @group Bdf_Api
 * @group Bdf_Api_Mapping
 * @group Bdf_Api_Mapping_Metadata
 * @group Bdf_Api_Mapping_Metadata_ParameterMetadata
 *
 * @coversDefaultClass Bdf\Api\Mapping\Metadata\ParameterMetadata
 */
class ParameterMetadataTest extends TestCase
{
    /**
     * @var ParameterMetadata
     */
    protected $metadata;

    /**
     * @var TypeMetadataInterface
     */
    protected $type;

    /**
     * @var MethodContext
     */
    protected $context;


    public function setUp(): void
    {
        $this->type = $this->createMock('Bdf\Api\Mapping\Metadata\Types\TypeMetadataInterface');
        $this->metadata = new ParameterMetadata('param1', $this->type);
        $this->context = $this->getMockBuilder('Bdf\Api\Mapping\MethodContext')
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     *
     */
    public function test_map()
    {
        $this->type->expects($this->once())->method('map')
            ->with('test', $this->context)
            ->will($this->returnValue('test'))
        ;

        $this->assertEquals(
            'test',
            $this->metadata->map('test', $this->context)
        );
    }

    /**
     *
     */
    public function test_map_with_filters()
    {
        $this->metadata->addFilter($filter1 = $this->createMock('Bdf\Api\Mapping\Filters\FilterInterface'));
        $this->metadata->addFilter($filter2 = $this->createMock('Bdf\Api\Mapping\Filters\FilterInterface'));

        $filter1->expects($this->once())->method('filter')
            ->with('test', $this->context)
            ->will($this->returnValue('filteredTest'))
        ;

        $filter2->expects($this->once())->method('filter')
            ->with('filteredTest', $this->context)
            ->will($this->returnValue('filtered_test'))
        ;

        $this->type->expects($this->once())->method('map')
            ->with('filtered_test', $this->context)
            ->will($this->returnArgument(0))
        ;

        $this->assertEquals(
            'filtered_test',
            $this->metadata->map('test', $this->context)
        );
    }

    /**
     *
     */
    public function test_map_with_transformer()
    {
        $this->metadata->setTransformer($transformer = $this->createMock('Bdf\Api\Mapping\Transformers\TransformerInterface'));

        $transformer->expects($this->once())->method('doTransform')
            ->with(666, $this->context)
            ->will($this->returnValue('number of the beast'))
        ;

        $this->type->expects($this->once())->method('map')
            ->with('number of the beast', $this->context)
            ->will($this->returnArgument(0))
        ;

        $this->assertEquals(
            'number of the beast',
            $this->metadata->map(666, $this->context)
        );
    }

    /**
     *
     */
    public function test_map_with_constraints()
    {
        $this->metadata->addConstraint($constraint1 = $this->createMock('Bdf\Api\Mapping\Constraints\ConstraintInterface'));
        $this->metadata->addConstraint($constraint2 = $this->createMock('Bdf\Api\Mapping\Constraints\ConstraintInterface'));

        $constraint1->expects($this->once())->method('validate')
            ->with('test', $this->context)
        ;

        $constraint2->expects($this->once())->method('validate')
            ->with('test', $this->context)
        ;

        $this->type->expects($this->once())->method('map')
            ->with('test', $this->context)
            ->will($this->returnArgument(0))
        ;

        $this->assertEquals(
            'test',
            $this->metadata->map('test', $this->context)
        );
    }

    /**
     *
     */
    public function test_map_with_all()
    {
        $this->metadata->addFilter($filter = $this->createMock('Bdf\Api\Mapping\Filters\FilterInterface'));

        $filter->expects($this->once())->method('filter')
            ->with('  666 ', $this->context)
            ->will($this->returnValue('666'))
        ;

        $this->metadata->setTransformer($transformer = $this->createMock('Bdf\Api\Mapping\Transformers\TransformerInterface'));

        $transformer->expects($this->once())->method('doTransform')
            ->with('666', $this->context)
            ->will($this->returnValue('number of the beast'))
        ;

        $this->metadata->addConstraint($constraint = $this->createMock('Bdf\Api\Mapping\Constraints\ConstraintInterface'));

        $constraint->expects($this->once())->method('validate')
            ->with('number of the beast', $this->context)
        ;

        $this->type->expects($this->once())->method('map')
            ->with('number of the beast', $this->context)
            ->will($this->returnArgument(0))
        ;

        $this->assertEquals(
            'number of the beast',
            $this->metadata->map('  666 ', $this->context)
        );
    }
}
