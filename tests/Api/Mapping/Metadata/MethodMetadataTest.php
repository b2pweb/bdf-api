<?php

namespace Bdf\Api\Mapping\Metadata;

use Bdf\Api\Mapping\Context;
use Bdf\Api\Mapping\Metadata\Types\OptionTypeMetadata;
use Bdf\Api\Mapping\Metadata\Types\SimpleTypeMetadata;
use Bdf\Api\Mapping\MethodContext;
use DI\Container;
use PHPUnit\Framework\TestCase;

/**
 * Class MethodMetadataTest
 *
 * @package Bdf\Api\Mapping\Metadata
 *
 * @group Bdf
 * @group Bdf_Api
 * @group Bdf_Api_Mapping
 * @group Bdf_Api_Mapping_Metadata
 * @group Bdf_Api_Mapping_Metadata_MethodMetadata
 *
 * @coversDefaultClass Bdf\Api\Mapping\Metadata\MethodMetadata
 */
class MethodMetadataTest extends TestCase
{
    /**
     * @var MethodMetadata
     */
    protected $metadata;


    public function setUp(): void
    {
        $this->metadata = new MethodMetadata('test');
    }

    /**
     *
     */
    public function test_map()
    {
        $this->assertEquals(
            [], $this->metadata->map([], new Context(new Container()))
        );
    }

    /**
     *
     */
    public function test_map_with_parameters()
    {
        $param1 = $this->getMockBuilder('Bdf\Api\Mapping\Metadata\ParameterMetadata')
            ->setMethods(['map'])
            ->setConstructorArgs(['param1', new SimpleTypeMetadata('string')])
            ->getMock();
        $param2 = $this->getMockBuilder('Bdf\Api\Mapping\Metadata\ParameterMetadata')
            ->setMethods(['map'])
            ->setConstructorArgs(['param2', new SimpleTypeMetadata('boolean')])
            ->getMock();

        $this->metadata->setParameters([$param1, $param2]);

        $param1->expects($this->once())->method('map')
            ->with('bonjour')
            ->will($this->returnValue('bonjour'))
        ;

        $param2->expects($this->once())->method('map')
            ->with(true)
            ->will($this->returnValue(true))
        ;

        $this->assertEquals(
            [
                'param1' => 'bonjour',
                'param2' => true,
            ],
            $this->metadata->map(
                [
                    'param1' => 'bonjour',
                    'param2' => true
                ],
                new Context(new Container())
            )
        );
    }

    /**
     *
     */
    public function test_map_with_constraints()
    {
        $constraint1 = $this->createMock('Bdf\Api\Mapping\Constraints\ConstraintInterface');
        $constraint2 = $this->createMock('Bdf\Api\Mapping\Constraints\ConstraintInterface');

        $this->metadata->addConstraint($constraint1);
        $this->metadata->addConstraint($constraint2);

        $constraint1->expects($this->once())->method('validate')
            ->with([]);

        $constraint2->expects($this->once())->method('validate')
            ->with([]);

        $this->metadata->map([], new Context(new Container()));
    }

    /**
     *
     */
    public function test_mapParameter()
    {
        $param1 = $this->getMockBuilder('Bdf\Api\Mapping\Metadata\ParameterMetadata')
            ->setMethods(['map'])
            ->setConstructorArgs(['param1', new SimpleTypeMetadata('string')])
            ->getMock();
        $param2 = $this->getMockBuilder('Bdf\Api\Mapping\Metadata\ParameterMetadata')
            ->setMethods(['map'])
            ->setConstructorArgs(['options', new OptionTypeMetadata()])
            ->getMock();

        $this->metadata->setParameters([$param1, $param2]);

        $context = new MethodContext(new Context(new Container()), $this->metadata, [
            'param1' => 'bonjour',
            'options' => [
                ['key' => 'option1', 'value' => 'value1']
            ]
        ]);

        $param1->expects($this->once())->method('map')
            ->with('bonjour', $context)
            ->will($this->returnValue('bonjour'))
        ;

        $param2->expects($this->never())->method('map');

        $this->metadata->setParameterOption('options');

        $this->assertEquals(
            'bonjour',
            $this->metadata->mapParameter(
                $param1,
                [
                    'param1' => 'bonjour',
                    'options' => [
                        [
                            'key' => 'option1',
                            'value' => 'value1'
                        ]
                    ]
                ],
                $context
            )
        );
    }

    /**
     *
     */
    public function test_mapParameter_with_option_parameter()
    {
        $param1 = $this->getMockBuilder('Bdf\Api\Mapping\Metadata\ParameterMetadata')
        ->setMethods(['map'])
        ->setConstructorArgs(['param1', new SimpleTypeMetadata('string')])
        ->getMock();
        $param2 = $this->getMockBuilder('Bdf\Api\Mapping\Metadata\ParameterMetadata')
            ->setMethods(['map'])
            ->setConstructorArgs(['options', new OptionTypeMetadata()])
            ->getMock();

        $this->metadata->setParameters([$param1, $param2]);

        $context = new MethodContext(new Context(new Container()), $this->metadata, [
            'param1' => 'bonjour',
            'options' => [
                ['key' => 'option1', 'value' => 'value1']
            ]
        ]);

        $param1->expects($this->never())->method('map');

        $param2->expects($this->once())->method('map')
            ->with(
                [['key' => 'option1', 'value' => 'value1']], $context
            )
            ->will($this->returnValue(['option1' => 'value1']))
        ;

        $this->metadata->setParameterOption('options');

        $this->assertEquals(
            ['option1' => 'value1'],
            $this->metadata->mapParameter(
                $param2,
                [
                    'param1' => 'bonjour',
                    'options' => [
                        [
                            'key' => 'option1',
                            'value' => 'value1'
                        ]
                    ]
                ],
                $context
            )
        );

        $this->assertEquals(['option1' => 'value1'], $context->getOptions());
    }

    /**
     *
     */
    public function test_mapParameter_with_same_call()
    {
        $param1 = $this->getMockBuilder('Bdf\Api\Mapping\Metadata\ParameterMetadata')
            ->setMethods(['map'])
            ->setConstructorArgs(['param1', new SimpleTypeMetadata('string')])
            ->getMock();
        $param2 = $this->getMockBuilder('Bdf\Api\Mapping\Metadata\ParameterMetadata')
            ->setMethods(['map'])
            ->setConstructorArgs(['options', new OptionTypeMetadata()])
            ->getMock();

        $this->metadata->setParameters([$param1, $param2]);

        $context = new MethodContext(new Context(new Container()), $this->metadata, [
            'param1' => 'bonjour',
            'options' => [
                ['key' => 'option1', 'value' => 'value1']
            ]
        ]);

        $param1->expects($this->once())->method('map')
            ->with('bonjour', $context)
            ->will($this->returnValue('bonjour'))
        ;

        $this->assertEquals('bonjour', $this->metadata->mapParameter($param1, ['param1' => 'bonjour', 'options' => []], $context));
        $this->assertEquals('bonjour', $this->metadata->mapParameter($param1, ['param1' => 'bonjour', 'options' => []], $context));
    }

    /**
     *
     */
    public function test_unmap_without_return()
    {
        $this->assertNull($this->metadata->unmap(null, new Context(new Container())));
        $this->assertNull($this->metadata->unmap('test', new Context(new Container())));
    }

    /**
     *
     */
    public function test_unmap()
    {
        $context = new Context(new Container());

        $this->metadata->setReturn(new ReturnMetadata(new SimpleTypeMetadata('string')));

        $this->assertEquals(
            'test', $this->metadata->unmap('test', $context)
        );
    }
}
