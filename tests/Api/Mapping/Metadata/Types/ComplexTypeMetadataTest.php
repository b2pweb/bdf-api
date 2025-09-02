<?php

namespace Bdf\Api\Mapping\Metadata\Types;

use Bdf\Api\Mapping\Context;
use Bdf\Api\Mapping\Fixtures\Location;
use Bdf\Api\Mapping\Fixtures\Offer;
use DI\Container;
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../Fixtures/Offer.php';
require_once __DIR__ . '/../../Fixtures/Location.php';

/**
 * Class ComplexTypeMetadataTest
 *
 * @package Bdf\Api\Mapping\Metadata\Types
 *
 * @group Bdf
 * @group Bdf_Api
 * @group Bdf_Api_Mapping
 * @group Bdf_Api_Mapping_Metadata
 * @group Bdf_Api_Mapping_Metadata_Types
 * @group Bdf_Api_Mapping_Metadata_Types_ComplexTypeMetadata
 *
 * @coversDefaultClass Bdf\Api\Mapping\Metadata\Types\ComplexTypeMetadata
 */
class ComplexTypeMetadataTest extends TestCase
{
    /**
     *
     */
    public function test_construct()
    {
        $metadata = new ComplexTypeMetadata('Test', 'Namespace\Test');

        $this->assertEquals('Test', $metadata->getName());
        $this->assertEquals('Namespace\Test', $metadata->getClass());
    }

    /**
     *
     */
    public function test_construct_with_properties()
    {
        $property1 = new DefaultPropertyMetadata('property1', new SimpleTypeMetadata('string'));
        $property2 = new DefaultPropertyMetadata('property2', new SimpleTypeMetadata('integer'));

        $metadata = new ComplexTypeMetadata('Test', 'Namespace\Test', [
            $property1, $property2
        ]);

        $this->assertEquals('Test', $metadata->getName());
        $this->assertEquals('Namespace\Test', $metadata->getClass());
        $this->assertEquals(
            [
                'property1' => $property1,
                'property2' => $property2,
            ],
            $metadata->getProperties()
        );

        $this->assertEquals($metadata, $property1->getParent());
        $this->assertEquals($metadata, $property2->getParent());
    }

    /**
     * @dataProvider mapProvider
     *
     * @param string $name
     * @param string $class
     * @param array $properties
     * @param object $source
     * @param object $target
     */
    public function test_map($name, $class, array $properties, $source, $target)
    {
        $metadata = new ComplexTypeMetadata($name, $class, $properties);

        $this->assertEquals($target, $metadata->map($source, new Context(new Container())));
    }

    /**
     * @return array
     */
    public function mapProvider()
    {
        return [
            [
                'Offer',
                'Bdf\Api\Mapping\Fixtures\Offer',
                [
                    new DefaultPropertyMetadata('name', new SimpleTypeMetadata('string')),
                    new EmbeddedPropertyMetadata('location', new ComplexTypeMetadata(
                        'Location', 'Bdf\Api\Mapping\Fixtures\Location', [
                            new DefaultPropertyMetadata('city', new SimpleTypeMetadata('string')),
                            new DefaultPropertyMetadata('country', new SimpleTypeMetadata('string')),
                        ]
                    )),
                ],
                (object) [
                    'name' => 'name1',
                    'city' => 'Cavaillon',
                    'country' => 'FR'
                ],
                new Offer('name1', new Location('Cavaillon', 'FR'))
            ],
        ];
    }

    /**
     * @dataProvider unmapProvider
     *
     * @param string $name
     * @param string $class
     * @param array $properties
     * @param object $source
     * @param object $target
     */
    public function test_unmap($name, $class, array $properties, $source, $target)
    {
        $metadata = new ComplexTypeMetadata($name, $class, $properties);

        $this->assertEquals($target, $metadata->unmap($source, new Context(new Container())));
    }

    /**
     * @return array
     */
    public function unmapProvider()
    {
        return [
            [
                'Offer',
                'Bdf\Api\Mapping\Fixtures\Offer',
                [
                    new DefaultPropertyMetadata('name', new SimpleTypeMetadata('string')),
                    new EmbeddedPropertyMetadata('location', new ComplexTypeMetadata(
                        'Location', 'Bdf\Api\Mapping\Fixtures\Location', [
                            new DefaultPropertyMetadata('city', new SimpleTypeMetadata('string')),
                            new DefaultPropertyMetadata('country', new SimpleTypeMetadata('string')),
                        ]
                    )),
                ],
                new Offer('name1', new Location('Cavaillon', 'FR')),
                (object) [
                    'name' => 'name1',
                    'city' => 'Cavaillon',
                    'country' => 'FR'
                ],
            ]
        ];
    }
}
