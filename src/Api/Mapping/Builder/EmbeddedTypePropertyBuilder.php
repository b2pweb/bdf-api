<?php

namespace Bdf\Api\Mapping\Builder;

use Bdf\Api\Mapping\Metadata\Types\EmbeddedPropertyMetadata;
use Bdf\Api\Mapping\Registry;

/**
 * Class EmbeddedTypePropertyBuilder
 *
 * @package Bdf\Api\Mapping\Builder
 */
class EmbeddedTypePropertyBuilder
{
    /**
     * @var Registry
     */
    protected $registry;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $type;

    /**
     * @var array
     */
    protected $properties = [];

    /**
     * @var array
     */
    protected $filters = [];

    /**
     * @var mixed
     */
    protected $transformer;

    /**
     * @var array
     */
    protected $constraints = [];


    /**
     * EmbeddedTypePropertyBuilder constructor.
     *
     * @param Registry|null $registry
     */
    public function __construct(?Registry $registry = null)
    {
        $this->registry = $registry ?: new Registry();
    }

    /**
     * @param string $name
     *
     * @return $this
     */
    public function name($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @param string $type
     *
     * @return $this
     */
    public function type($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @param array $properties
     *
     * @return $this
     */
    public function properties(array $properties)
    {
        $this->properties = $properties;

        return $this;
    }

    /**
     * @param mixed $filter
     * @param bool $append
     *
     * @return $this
     */
    public function filter($filter, $append = true)
    {
        if ($append) {
            $this->filters[] = $filter;
        } else {
            array_unshift($this->filters, $filter);
        }

        return $this;
    }

    /**
     * @param mixed $transformer
     *
     * @return $this
     */
    public function transformer($transformer)
    {
        $this->transformer = $transformer;

        return $this;
    }

    /**
     * @param mixed $constraint
     * @param bool $append
     *
     * @return $this
     */
    public function constraint($constraint, $append = true)
    {
        if ($append) {
            $this->constraints[] = $constraint;
        } else {
            array_unshift($this->constraints, $constraint);
        }

        return $this;
    }

    /**
     * @param ServiceBuilder $serviceBuilder
     *
     * @return EmbeddedPropertyMetadata
     */
    public function build(ServiceBuilder $serviceBuilder)
    {
        $metadata = new EmbeddedPropertyMetadata($this->name, $serviceBuilder->buildType($this->type));
        $metadata->setPropertyMapping($this->properties);

        // Filters
        foreach ($this->filters as $filter) {
            $metadata->addFilter($this->registry->getFilter($filter));
        }

        // Transformer
        if ($this->transformer) {
            $metadata->setTransformer($this->registry->getTransformer($this->transformer));
        }

        // Constraints
        foreach ($this->constraints as $constraint) {
            $metadata->addConstraint($this->registry->getConstraint($constraint));
        }

        return $metadata;
    }
}
