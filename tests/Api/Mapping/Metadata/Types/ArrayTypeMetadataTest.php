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
 * Class ArrayTypeMetadataTest
 *
 * @package Bdf\Api\Mapping\Metadata\Types
 *
 * @group Bdf
 * @group Bdf_Api
 * @group Bdf_Api_Mapping
 * @group Bdf_Api_Mapping_Metadata
 * @group Bdf_Api_Mapping_Metadata_Types
 * @group Bdf_Api_Mapping_Metadata_Types_ArrayTypeMetadata
 *
 * @coversDefaultClass Bdf\Api\Mapping\Metadata\Types\ArrayTypeMetadata
 */
class ArrayTypeMetadataTest extends TestCase
{
    /**
     * @dataProvider typesProvider
     *
     * @param TypeMetadataInterface $type
     * @param string $expectedName
     */
    public function test_getters(TypeMetadataInterface $type, $expectedName)
    {
        $metadata = new ArrayTypeMetadata($type);

        $this->assertEquals($type, $metadata->getInternalType());
        $this->assertEquals($expectedName, $metadata->getName());
    }

    /**
     * @return array
     */
    public function typesProvider()
    {
        return [
            [
                new SimpleTypeMetadata('boolean'),
                'boolean[]'
            ],
            [
                new ArrayTypeMetadata(new SimpleTypeMetadata('int')),
                'int[][]'
            ],
            [
                new ComplexTypeMetadata('Offer', 'Bdf\Api\Mapping\Fixtures\Offer'),
                'Offer[]'
            ],
            [
                new ArrayTypeMetadata(new ComplexTypeMetadata('Offer', 'Bdf\Api\Mapping\Fixtures\Offer')),
                'Offer[][]'
            ]
        ];
    }

    /**
     * @dataProvider mapProvider
     *
     * @param TypeMetadataInterface $type
     * @param array $source
     * @param array $target
     */
    public function test_map(TypeMetadataInterface $type, array $source, array $target)
    {
        $metadata = new ArrayTypeMetadata($type);

        $this->assertEquals($target, $metadata->map($source, new Context(new Container())));
    }

    /**
     * @return array
     */
    public function mapProvider()
    {
        return [
            [
                new SimpleTypeMetadata('string'),
                ['string1', 'string2', 'string3'],
                ['string1', 'string2', 'string3'],
            ],
            [
                new ArrayTypeMetadata(new SimpleTypeMetadata('int')),
                [[1, 2], [3, 5]],
                [[1, 2], [3, 5]],
            ],
            [
                new ComplexTypeMetadata('Offer', 'Bdf\Api\Mapping\Fixtures\Offer', [
                    new DefaultPropertyMetadata('name', new SimpleTypeMetadata('string')),
                    new EmbeddedPropertyMetadata('location', new ComplexTypeMetadata(
                        'Location', 'Bdf\Api\Mapping\Fixtures\Location', [
                            new DefaultPropertyMetadata('city', new SimpleTypeMetadata('string')),
                            new DefaultPropertyMetadata('country', new SimpleTypeMetadata('string')),
                        ]
                    ))
                ]),
                [
                    (object) [
                        'name' => 'name1',
                        'city' => 'Cavaillon',
                        'country' => 'FR'
                    ],
                    (object) [
                        'name' => 'name2',
                        'city' => 'Cavaillon',
                        'country' => 'FR'
                    ]
                ],
                [new Offer('name1', new Location('Cavaillon', 'FR')), new Offer('name2', new Location('Cavaillon', 'FR'))]
            ]
        ];
    }

    /**
     * @dataProvider unmapProvider
     *
     * @param TypeMetadataInterface $type
     * @param array $source
     * @param array $target
     */
    public function test_unmap(TypeMetadataInterface $type, array $source, array $target)
    {
        $metadata = new ArrayTypeMetadata($type);

        $this->assertEquals($target, $metadata->unmap($source, new Context(new Container())));
    }

    /**
     * @return array
     */
    public function unmapProvider()
    {
        return [
            [
                new SimpleTypeMetadata('string'),
                ['string1', 'string2', 'string3'],
                ['string1', 'string2', 'string3'],
            ],
            [
                new ArrayTypeMetadata(new SimpleTypeMetadata('int')),
                [[1, 2], [3, 5]],
                [[1, 2], [3, 5]],
            ],
            [
                new ComplexTypeMetadata('Offer', 'Bdf\Api\Mapping\Fixtures\Offer', [
                    new DefaultPropertyMetadata('name', new SimpleTypeMetadata('string')),
                    new EmbeddedPropertyMetadata('location', new ComplexTypeMetadata(
                        'Location', 'Bdf\Api\Mapping\Fixtures\Location', [
                            new DefaultPropertyMetadata('city', new SimpleTypeMetadata('string')),
                            new DefaultPropertyMetadata('country', new SimpleTypeMetadata('string')),
                        ]
                    ))
                ]),
                [new Offer('name1', new Location('Cavaillon', 'FR')), new Offer('name2', new Location('Cavaillon', 'FR'))],
                [
                    (object) [
                        'name' => 'name1',
                        'city' => 'Cavaillon',
                        'country' => 'FR'
                    ],
                    (object) [
                        'name' => 'name2',
                        'city' => 'Cavaillon',
                        'country' => 'FR'
                    ]
                ]
            ]
        ];
    }
}
