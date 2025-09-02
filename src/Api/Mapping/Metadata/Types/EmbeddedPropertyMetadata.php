<?php

namespace Bdf\Api\Mapping\Metadata\Types;

use Bdf\Api\Mapping\ComplexTypeContext;

/**
 * Class EmbeddedPropertyMetadata
 *
 * @package Bdf\Api\Mapping\Metadata\Types
 */
class EmbeddedPropertyMetadata extends PropertyMetadata
{
    /**
     * @var array
     */
    protected $propertyMapping = [];

    /**
     * {@inheritdoc}
     */
    public function map($source, $target, ComplexTypeContext $context)
    {
        if (!$context->has($this->name)) {
            $result = $this->type->map(
                $this->applyFilters(
                    $this->mapProperties($source), $context
                ), $context
            );

            if ($this->transformer) {
                $result = $this->transformer->doTransform($result, $context);
            }

            $this->validateConstraints($result, $context);

            $context->set($this->name, $result);
        }

        $this->write($target, $context->get($this->name));
    }

    /**
     * {@inheritdoc}
     */
    public function unmap($source, $target, ComplexTypeContext $context)
    {
        if (!$context->has($this->name)) {
            $source = $this->read($source);

            if ($this->transformer) {
                $source = $this->transformer->undoTransform($source, $context);
            }

            $context->set(
                $this->name, $this->unmapProperties($this->type->unmap($source, $context))
            );
        }

        foreach ($context->get($this->name) as $property => $value) {
            $target->$property = $value;
        }
    }

    /**
     * @param object $source
     *
     * @return object
     */
    protected function mapProperties($source)
    {
        $result = clone $source;

        foreach ($this->propertyMapping as $name => $mappedName) {
            if (property_exists($result, $name)) {
                $result->$mappedName = $result->$name;

                unset($result->$name);
            }
        }

        return $result;
    }

    /**
     * @param object $source
     *
     * @return object
     */
    protected function unmapProperties($source)
    {
        $result = clone $source;

        foreach ($this->propertyMapping as $name => $mappedName) {
            if (property_exists($result, $mappedName)) {
                $result->$name = $result->$mappedName;

                unset($result->$mappedName);
            }
        }

        return $result;
    }

    /**
     * @param array $propertyMapping
     *
     * @return $this
     */
    public function setPropertyMapping(array $propertyMapping)
    {
        $this->propertyMapping = $propertyMapping;

        return $this;
    }

    /**
     * Gets the property mapping
     */
    public function getPropertyMapping(): array
    {
        return $this->propertyMapping;
    }
}
