<?php

namespace Bdf\Api\Mapping\Metadata;

use Bdf\Api\Mapping\Metadata\Types\TypeMetadataInterface;
use Bdf\Api\Mapping\MethodContext;
use Bdf\Api\Mapping\Constraints\ConstraintInterface;
use Bdf\Api\Mapping\Filters\FilterInterface;
use Bdf\Api\Mapping\Transformers\TransformerInterface;

/**
 * Class ParameterMetadata
 *
 * @package Bdf\Api\Mapping\Metadata
 */
class ParameterMetadata
{
    /**
     * @var string
     */
    protected $name;

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
     * ParameterMetadata constructor.
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
     * @param string $name
     *
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;

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
     * @param TypeMetadataInterface $type
     *
     * @return $this
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
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
     * @param MethodContext $context
     *
     * @return mixed
     */
    public function map($source, MethodContext $context)
    {
        foreach ($this->filters as $filter) {
            $source = $filter->filter($source, $context);
        }

        if ($this->transformer) {
            $source = $this->transformer->doTransform($source, $context);
        }

        $result = $this->type->map($source, $context);

        foreach ($this->constraints as $constraint) {
            $constraint->validate($result, $context);
        }

        return $result;
    }

    /**
     * @param mixed $source
     *
     * @return mixed
     */
    public function unmap($source)
    {
       return $source;
    }
}
