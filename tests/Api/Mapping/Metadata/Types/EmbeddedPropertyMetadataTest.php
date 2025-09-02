<?php

namespace Bdf\Api\Mapping\Metadata\Types;

use Bdf\Api\Mapping\Fixtures\Location;
use Bdf\Api\Mapping\ComplexTypeContext;
use Bdf\Api\Mapping\Fixtures\Offer;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Assert;

require_once __DIR__ . '/../../Fixtures/Offer.php';
require_once __DIR__ . '/../../Fixtures/Location.php';

/**
 * Class EmbeddedPropertyMetadataTest
 *
 * @package Bdf\Api\Mapping\Metadata\Types
 *
 * @group Bdf
 * @group Bdf_Api
 * @group Bdf_Api_Mapping
 * @group Bdf_Api_Mapping_Metadata
 * @group Bdf_Api_Mapping_Metadata_Types
 * @group Bdf_Api_Mapping_Metadata_Types_EmbeddedPropertyMetadata
 *
 * @coversDefaultClass Bdf\Api\Mapping\Metadata\Types\DefaultPropertyMetadata
 */
class EmbeddedPropertyMetadataTest extends TestCase
{
    /**
     *
     */
    public function test_transformer()
    {
        $transformer = $this->createMock('Bdf\Api\Mapping\Transformers\TransformerInterface');

        $metadata = new EmbeddedPropertyMetadata('name', new ComplexTypeMetadata('Location', 'Bdf\Api\Mapping\Fixtures\Location'));

        $metadata->setTransformer($transformer);

        $this->assertEquals($transformer, $metadata->getTransformer());
    }

    /**
     *
     */
    public function test_addConstraint()
    {
        $constraint1 = $this->createMock('Bdf\Api\Mapping\Constraints\ConstraintInterface');
        $constraint2 = $this->createMock('Bdf\Api\Mapping\Constraints\ConstraintInterface');

        $metadata = new EmbeddedPropertyMetadata('name', new ComplexTypeMetadata('Location', 'Bdf\Api\Mapping\Fixtures\Location'));

        $metadata->addConstraint($constraint1);
        $metadata->addConstraint($constraint2);

        $this->assertEquals(
            [$constraint1, $constraint2],
            $metadata->getConstraints()
        );
    }

    /**
     *
     */
    public function test_addFilter()
    {
        $filter1 = $this->createMock('Bdf\Api\Mapping\Filters\FilterInterface');
        $filter2 = $this->createMock('Bdf\Api\Mapping\Filters\FilterInterface');

        $metadata = new EmbeddedPropertyMetadata('name', new ComplexTypeMetadata('Location', 'Bdf\Api\Mapping\Fixtures\Location'));

        $metadata->addFilter($filter1);
        $metadata->addFilter($filter2);

        $this->assertEquals(
            [$filter1, $filter2],
            $metadata->getFilters()
        );
    }

    /**
     *
     */
    public function test_map()
    {
        $locationProperty = $this->getOfferTypeMetadata()->getProperty('location');

        $source = (object) [
            'fromCity' => 'Cavaillon',
            'fromCountry' => 'France',
        ];

        $target = (new \ReflectionClass('Bdf\Api\Mapping\Fixtures\Offer'))->newInstanceWithoutConstructor();

        $locationProperty->map(
            $source, $target, new ComplexTypeContext($this->createMock('Bdf\Api\Mapping\ContextInterface'), $source, $target, function() {})
        );

        $this->assertEquals(new Location('Cavaillon', 'France'), $target->getLocation());
    }

    /**
     *
     */
    public function test_unmap()
    {
        $locationProperty = $this->getOfferTypeMetadata()->getProperty('location');

        $source = new Offer('Testouille', new Location('Cavaillon', 'France'));
        $target = new \stdClass();

        $locationProperty->unmap(
            $source, $target, new ComplexTypeContext($this->createMock('Bdf\Api\Mapping\ContextInterface'), $source, $target, function() {})
        );

        $this->assertEquals(
            (object) ['fromCity' => 'Cavaillon', 'fromCountry' => 'France'],
            $target
        );
    }

    /**
     * @return ComplexTypeMetadata
     */
    protected function getOfferTypeMetadata()
    {
        return new ComplexTypeMetadata('Offer', 'Bdf\Api\Mapping\Fixtures\Offer', [
            new DefaultPropertyMetadata('name', new SimpleTypeMetadata('string')),
            (new EmbeddedPropertyMetadata('location', new ComplexTypeMetadata(
                'Location', 'Bdf\Api\Mapping\Fixtures\Location', [
                    new DefaultPropertyMetadata('city', new SimpleTypeMetadata('string')),
                    new DefaultPropertyMetadata('country', new SimpleTypeMetadata('string')),
                ]
            )))->setPropertyMapping(['fromCity' => 'city', 'fromCountry' => 'country'])
        ]);
    }
}
