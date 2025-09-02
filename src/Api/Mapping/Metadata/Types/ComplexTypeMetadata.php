<?php

namespace Bdf\Api\Mapping\Metadata\Types;

use Bdf\Api\Mapping\ComplexTypeContext;
use Bdf\Api\Mapping\ContextInterface;

/**
 * Class ComplexTypeMetadata
 *
 * @package Bdf\Api\Mapping\Metadata
 */
class ComplexTypeMetadata implements TypeMetadataInterface
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $class;

    /**
     * @var PropertyMetadata[]
     */
    protected $properties;


    /**
     * ComplexTypeMetadata constructor.
     *
     * @param string $name
     * @param string $class
     * @param PropertyMetadata[] $properties
     */
    public function __construct($name, $class, array $properties = [])
    {
        $this->name = $name;
        $this->class = $class;

        $this->setProperties($properties);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getClass()
    {
        return $this->class;
    }

    /**
     * @return PropertyMetadata[]
     */
    public function getProperties()
    {
        return $this->properties;
    }

    /**
     * @param array $properties
     *
     * @return $this
     */
    public function setProperties(array $properties)
    {
        $this->properties = [];

        foreach ($properties as $property) {
            $this->addProperty($property);
        }

        return $this;
    }

    /**
     * @param string $name
     *
     * @return PropertyMetadata
     */
    public function getProperty($name)
    {
        return $this->properties[$name];
    }

    /**
     * @param PropertyMetadata $property
     *
     * @return $this
     */
    public function addProperty(PropertyMetadata $property)
    {
        $property->setParent($this);

        $this->properties[$property->getName()] = $property;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function map($source, ContextInterface $context)
    {
        $target = (new \ReflectionClass($this->class))->newInstanceWithoutConstructor();

        $context = new ComplexTypeContext(
            $context, $source, $target, function($name, ComplexTypeContext $context) {
                $this->getProperty($name)->map($context->getSource(), $context->getTarget(), $context);
            }
        );

        foreach ($this->properties as $property) {
            $property->map($source, $target, $context);
        }

        return $target;
    }

    /**
     * {@inheritdoc}
     */
    public function unmap($source, ContextInterface $context)
    {
        if ($source === null) {
            return null;
        }

        $target = new \stdClass();

        $context = new ComplexTypeContext(
            $context, $source, $target, function($name, ComplexTypeContext $context) {
                $this->getProperty($name)->unmap($context->getSource(), $context->getTarget(), $context);
            }
        );

        foreach ($this->properties as $property) {
            $property->unmap($source, $target, $context);
        }

        return $target;
    }
}
