<?php

namespace Bdf\Api\Mapping\Builder;

use Bdf\Api\Mapping\Registry;
use Bdf\Api\Mapping\Metadata\ParameterMetadata;

/**
 * Class ParameterBuilder
 *
 * @package Bdf\Api\Mapping\Builder
 */
class ParameterBuilder
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
     * ParameterBuilder constructor.
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
     * @param mixed $filter
     *
     * @return $this
     */
    public function filter($filter)
    {
        $this->filters[] = $filter;

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
     *
     * @return $this
     */
    public function constraint($constraint)
    {
        $this->constraints[] = $constraint;

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
     * @param ServiceBuilder $serviceBuilder
     *
     * @return ParameterMetadata
     */
    public function build(ServiceBuilder $serviceBuilder)
    {
        $metadata = new ParameterMetadata($this->name, $serviceBuilder->buildType($this->type));

        foreach ($this->filters as $filter) {
            $metadata->addFilter($this->registry->getFilter($filter));
        }

        if ($this->transformer) {
            $metadata->setTransformer($this->registry->getTransformer($this->transformer));
        }

        foreach ($this->constraints as $constraint) {
            $metadata->addConstraint($this->registry->getConstraint($constraint));
        }

        return $metadata;
    }
}
