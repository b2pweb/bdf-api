<?php

namespace Bdf\Api\Mapping\Metadata\Types;

use Bdf\Api\Mapping\ComplexTypeContext;
use Bdf\Api\Mapping\Constraints\ConstraintInterface;
use Bdf\Api\Mapping\Filters\FilterInterface;
use Bdf\Api\Mapping\Transformers\TransformerInterface;

/**
 * Class PropertyMetadata
 *
 * @package Bdf\Api\Mapping\Metadata\Types
 */
abstract class PropertyMetadata
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var ComplexTypeMetadata
     */
    protected $parent;

    /**
     * @var TypeMetadataInterface
     */
    protected $type;

    /**
     * @var FilterInterface[]
     */
    protected $filters = [];

    /**
     * @var TransformerInterface
     */
    protected $transformer;

    /**
     * @var ConstraintInterface[]
     */
    protected $constraints = [];


    /**
     * PropertyMetadata constructor.
     *
     * @param string $name
     * @param TypeMetadataInterface $type
     */
    public function __construct($name, TypeMetadataInterface $type)
    {
        $this->name = $name;
        $this->type = $type;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return ComplexTypeMetadata
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * @param ComplexTypeMetadata $metadata
     *
     * @return $this
     */
    public function setParent(ComplexTypeMetadata $metadata)
    {
        $this->parent = $metadata;

        return $this;
    }

    /**
     * @return TypeMetadataInterface
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return FilterInterface[]
     */
    public function getFilters()
    {
        return $this->filters;
    }

    /**
     * @param FilterInterface $filter
     *
     * @return $this
     */
    public function addFilter(FilterInterface $filter)
    {
        $this->filters[] = $filter;

        return $this;
    }

    /**
     * @return TransformerInterface
     */
    public function getTransformer()
    {
        return $this->transformer;
    }

    /**
     * @param TransformerInterface $transformer
     *
     * @return $this
     */
    public function setTransformer(TransformerInterface $transformer)
    {
        $this->transformer = $transformer;

        return $this;
    }

    /**
     * @return ConstraintInterface[]
     */
    public function getConstraints()
    {
        return $this->constraints;
    }

    /**
     * @param ConstraintInterface $constraint
     *
     * @return $this
     */
    public function addConstraint(ConstraintInterface $constraint)
    {
        $this->constraints[] = $constraint;

        return $this;
    }

    /**
     * @param mixed $source
     * @param object $target
     * @param ComplexTypeContext $context
     *
     * @return mixed
     */
    abstract public function map($source, $target, ComplexTypeContext $context);

    /**
     * @param mixed $source
     * @param object $target
     * @param ComplexTypeContext $context
     *
     * @return
     */
    abstract public function unmap($source, $target, ComplexTypeContext $context);

    /**
     * @param mixed $source
     * @param ComplexTypeContext $context
     *
     * @return mixed
     */
    protected function applyFilters($source, ComplexTypeContext $context)
    {
        foreach ($this->filters as $filter) {
            $source = $filter->filter($source, $context);
        }

        return $source;
    }

    /**
     * @param mixed $value
     * @param ComplexTypeContext $context
     *
     * @throws \Exception
     */
    protected function validateConstraints($value, ComplexTypeContext $context)
    {
        foreach ($this->constraints as $constraint) {
            $constraint->validate($value, $context);
        }
    }

    protected function read(object $source): mixed
    {
        $className = $this->parent->getClass();
        $propertyName = $this->name;

        if ($className === \stdClass::class) {
            return $source->{$propertyName};
        }

        $accessor = static fn () => $source->{$propertyName};
        $accessor = $accessor->bindTo(null, $className);

        return $accessor();
    }

    protected function write(object $source, mixed $value): mixed
    {
        $className = $this->parent->getClass();
        $propertyName = $this->name;

        if ($className === \stdClass::class) {
            return $source->{$propertyName} = $value;
        }

        $accessor = static function () use ($source, $propertyName, $value) {
            $source->{$propertyName} = $value;
        };
        $accessor = $accessor->bindTo(null, $className);

        return $accessor();
    }
}
