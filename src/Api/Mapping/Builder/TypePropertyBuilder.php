<?php

namespace Bdf\Api\Mapping\Builder;

use Bdf\Api\Mapping\Metadata\Types\DefaultPropertyMetadata;
use Bdf\Api\Mapping\Metadata\Types\PropertyMetadata;
use Bdf\Api\Mapping\Registry;


/**
 * Class TypePropertyBuilder
 *
 * @package Bdf\Api\Mapping\Builder
 */
class TypePropertyBuilder
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $type = 'string';

    /**
     * @var string
     */
    protected $definedBy;

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
     * @var Registry
     */
    protected $registry;


    /**
     * TypePropertyBuilder constructor.
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
     * @param string $definedBy
     *
     * @return $this
     */
    public function definedBy($definedBy)
    {
        $this->definedBy = $definedBy;

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
     * Overload type rather than adding brackets for build optimisation
     *
     * @return $this
     */
    public function collection()
    {
        $this->type = 'array';

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
     * @return DefaultPropertyMetadata
     */
    public function build(ServiceBuilder $serviceBuilder)
    {
        $metadata = new DefaultPropertyMetadata($this->name, $serviceBuilder->buildType($this->type));
        $metadata->setDefinedBy($this->definedBy);

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
