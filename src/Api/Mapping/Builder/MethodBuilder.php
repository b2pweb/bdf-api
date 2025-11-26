<?php

namespace Bdf\Api\Mapping\Builder;

use Bdf\Api\Mapping\Registry;
use Bdf\Api\Mapping\Metadata\MethodMetadata;
use Bdf\Api\Mapping\Metadata\Types\OptionTypeMetadata;

/**
 * MethodBuilder
 *
 * @package Bdf\Api\Mapping\Builder
 */
class MethodBuilder
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
     * @var ParameterBuilder[]
     */
    protected $parameters = [];

    /**
     * @var ReturnBuilder
     */
    protected $returnType;

    /**
     * @var array
     */
    protected $constraints = [];


    /**
     * MethodBuilder constructor.
     *
     * @param Registry|null $registry
     */
    public function __construct(?Registry $registry = null)
    {
        $this->registry = $registry ?: new Registry();
    }

    /**
     * Set the method name
     *
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
     * Add an "object" property
     *
     * @param string $name
     *
     * @return ParameterBuilder
     */
    public function object($name)
    {
        return $this->parameter($name, 'object');
    }

    /**
     * Add an "mixed" property
     *
     * @param string $name
     *
     * @return ParameterBuilder
     */
    public function mixed($name)
    {
        return $this->parameter($name, 'mixed');
    }

    /**
     * Add an "string" property
     *
     * @param string $name
     *
     * @return ParameterBuilder
     */
    public function string($name)
    {
        return $this->parameter($name, 'string');
    }

    /**
     * Add an "integer" property
     *
     * @param string $name
     *
     * @return ParameterBuilder
     */
    public function integer($name)
    {
        return $this->parameter($name, 'integer');
    }

    /**
     * Add an "long" property
     *
     * @param string $name
     *
     * @return ParameterBuilder
     */
    public function long($name)
    {
        return $this->parameter($name, 'long');
    }

    /**
     * Add an "float" property
     *
     * @param string $name
     *
     * @return ParameterBuilder
     */
    public function float($name)
    {
        return $this->parameter($name, 'float');
    }

    /**
     * Add an "double" property
     *
     * @param string $name
     *
     * @return ParameterBuilder
     */
    public function double($name)
    {
        return $this->parameter($name, 'double');
    }

    /**
     * Add an "boolean" property
     *
     * @param string $name
     *
     * @return ParameterBuilder
     */
    public function boolean($name)
    {
        return $this->parameter($name, 'boolean');
    }

    /**
     * Add an "array" property
     *
     * @param string $name
     *
     * @return ParameterBuilder
     */
    public function simpleArray($name)
    {
        return $this->parameter($name, 'array');
    }

    /**
     * Add an "option type" property
     *
     * @param string $name
     *
     * @return ParameterBuilder
     */
    public function options($name)
    {
        return $this->parameter($name, 'options');
    }

    /**
     * Add a parameter
     *
     * @param string $name
     * @param string $type
     *
     * @return ParameterBuilder
     */
    public function parameter($name, $type)
    {
        $builder = new ParameterBuilder($this->registry);

        $builder->name($name);
        $builder->type($type);

        return $this->parameters[$name] = $builder;
    }

    /**
     * Add a constraint
     *
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
     * Set the return type
     *
     * @param string $type
     *
     * @return ReturnBuilder
     */
    public function returns($type)
    {
        $builder = new ReturnBuilder($this->registry);

        $builder->type($type);

        return $this->returnType = $builder;
    }

    /**
     * Build the method metadata
     *
     * @param ServiceBuilder $serviceBuilder
     *
     * @return MethodMetadata
     */
    public function build(ServiceBuilder $serviceBuilder)
    {
        $metadata = new MethodMetadata($this->name);

        foreach ($this->parameters as $builder) {
            $parameterMetadata = $builder->build($serviceBuilder);

            if ($parameterMetadata->getType() instanceof OptionTypeMetadata) {
                $metadata->setParameterOption($parameterMetadata->getName());
            }

            $metadata->addParameter($parameterMetadata);
        }

        if ($this->returnType) {
            $metadata->setReturn($this->returnType->build($serviceBuilder));
        }

        // Constraints
        foreach ($this->constraints as $constraint) {
            $metadata->addConstraint($this->registry->getConstraint($constraint));
        }

        return $metadata;
    }
}
