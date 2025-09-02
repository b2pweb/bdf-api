<?php

namespace Bdf\Api\Mapping\Metadata\Types;

use Bdf\Api\Mapping\ComplexTypeContext;
use Bdf\Api\Mapping\Context;
use Bdf\Api\Mapping\ContextWrapperInterface;
use Bdf\Api\Mapping\Filters\ClosureFilter;
use Bdf\Api\Mapping\Fixtures\Location;
use Bdf\Api\Mapping\Fixtures\Offer;
use DI\Container;
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../Fixtures/Offer.php';
require_once __DIR__ . '/../../Fixtures/Location.php';

/**
 * Class DefaultPropertyMetadataTest
 *
 * @package Bdf\Api\Mapping\Metadata\Types
 *
 * @group Bdf
 * @group Bdf_Api
 * @group Bdf_Api_Mapping
 * @group Bdf_Api_Mapping_Metadata
 * @group Bdf_Api_Mapping_Metadata_Types
 * @group Bdf_Api_Mapping_Metadata_Types_DefaultPropertyMetadata
 */
class DefaultPropertyMetadataTest extends TestCase
{
    /**
     *
     */
    public function test_getters_setters()
    {
        $metadata = new DefaultPropertyMetadata('name', new SimpleTypeMetadata('int'));

        $metadata
            ->setDefinedBy('test')
            ->setNillable(true)
        ;

        $this->assertSame('test', $metadata->getDefinedBy());
        $this->assertSame(true, $metadata->isNillable());
    }

    /**
     *
     */
    public function test_transformer()
    {
        $transformer = $this->createMock('Bdf\Api\Mapping\Transformers\TransformerInterface');

        $metadata = new DefaultPropertyMetadata('name', new SimpleTypeMetadata('int'));

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

        $metadata = new DefaultPropertyMetadata('name', new SimpleTypeMetadata('int'));

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

        $metadata = new DefaultPropertyMetadata('name', new SimpleTypeMetadata('int'));

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
        $nameProperty = $this->getOfferTypeMetadata()->getProperty('name');

        $nameProperty
            ->addFilter(new ClosureFilter(function ($value, ContextWrapperInterface $context) {
                if (!$value) {
                    $value = $context->getOption('fallbacks.name');
                }

                return $value;
            }))
            ->addFilter(new ClosureFilter(function ($value) {
                return strtoupper($value);
            }))
            ->addFilter(new ClosureFilter(function ($value) {
                return trim($value);
            }))
        ;

        $source = (object) [
            'name' => '     bonjour     ',
            'location' => (object) [
                'city' => 'Carpentras',
                'country' => 'France'
            ]
        ];
        $target = (new \ReflectionClass('Bdf\Api\Mapping\Fixtures\Offer'))->newInstanceWithoutConstructor();

        $nameProperty->map($source, $target, new ComplexTypeContext(
            new Context(new Container()), $source, $target, function() {}
        ));

        $this->assertEquals('BONJOUR', $target->getName());
    }

    /**
     *
     */
    public function test_unmap()
    {
        $nameProperty = $this->getOfferTypeMetadata()->getProperty('name');

        $source = new Offer('Testouille', new Location('Carpentras', 'France'));
        $target = new \stdClass();

        $nameProperty->unmap($source, $target, new ComplexTypeContext(
            new Context(new Container()), $source, $target, function() {}
        ));

        $this->assertEquals('Testouille', $target->name);
    }

    /**
     * @return ComplexTypeMetadata
     */
    protected function getOfferTypeMetadata()
    {
        return new ComplexTypeMetadata('Offer', 'Bdf\Api\Mapping\Fixtures\Offer', [
            new DefaultPropertyMetadata('name', new SimpleTypeMetadata('string')),
            new DefaultPropertyMetadata('location', new ComplexTypeMetadata('Location', 'Bdf\Api\Mapping\Fixtures\Location', [
                new DefaultPropertyMetadata('city', new SimpleTypeMetadata('string')),
                new DefaultPropertyMetadata('country', new SimpleTypeMetadata('string')),
            ])),
        ]);
    }
}
