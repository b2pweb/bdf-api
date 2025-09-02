<?php

namespace Bdf\Api\Definition\Builder;

use Bdf\Api\Definition\MethodDefinition;

/**
 * Class MethodDefinitionBuilder
 *
 * @package Bdf\Api\Definition\Builder
 */
class MethodDefinitionBuilder
{
    /**
     * @var string
     */
    protected $description;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var ParameterDefinitionBuilder[]
     */
    protected $parameters = [];

    /**
     * @var string
     */
    protected $returnType;

    /**
     * @var bool
     */
    protected $throwingException = false;


    /**
     * Add a method description
     *
     * @param string $description
     *
     * @return $this
     */
    public function description($description)
    {
        $this->description = $description;

        return $this;
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
     * @param mixed  $default
     *
     * @return ParameterDefinitionBuilder
     */
    public function object($name, $default = null)
    {
        return $this->parameter($name, 'object', $default);
    }

    /**
     * Add an "mixed" property
     *
     * @param string $name
     * @param mixed  $default
     *
     * @return ParameterDefinitionBuilder
     */
    public function mixed($name, $default = null)
    {
        return $this->parameter($name, 'mixed', $default);
    }

    /**
     * Add an "string" property
     *
     * @param string $name
     * @param mixed  $default
     *
     * @return ParameterDefinitionBuilder
     */
    public function string($name, $default = null)
    {
        return $this->parameter($name, 'string', $default);
    }

    /**
     * Add an "integer" property
     *
     * @param string $name
     * @param mixed  $default
     *
     * @return ParameterDefinitionBuilder
     */
    public function integer($name, $default = null)
    {
        return $this->parameter($name, 'integer', $default);
    }

    /**
     * Add an "long" property
     *
     * @param string $name
     * @param mixed  $default
     *
     * @return ParameterDefinitionBuilder
     */
    public function long($name, $default = null)
    {
        return $this->parameter($name, 'long', $default);
    }

    /**
     * Add an "float" property
     *
     * @param string $name
     * @param mixed  $default
     *
     * @return ParameterDefinitionBuilder
     */
    public function float($name, $default = null)
    {
        return $this->parameter($name, 'float', $default);
    }

    /**
     * Add an "double" property
     *
     * @param string $name
     * @param mixed  $default
     *
     * @return ParameterDefinitionBuilder
     */
    public function double($name, $default = null)
    {
        return $this->parameter($name, 'double', $default);
    }

    /**
     * Add an "boolean" property
     *
     * @param string $name
     * @param mixed  $default
     *
     * @return ParameterDefinitionBuilder
     */
    public function boolean($name, $default = null)
    {
        return $this->parameter($name, 'boolean', $default);
    }

    /**
     * Add an "array" property supported only by rpc-encoded style
     *
     * @param string $name
     * @param mixed  $default
     *
     * @return ParameterDefinitionBuilder
     */
    public function rpcArray($name, $default = null)
    {
        return $this->parameter($name, 'array', $default);
    }

    /**
     * Add an "option type" property
     *
     * @param string $name
     * @param string $type
     *
     * @return ParameterDefinitionBuilder
     */
    public function options($name, $type = 'HandleOption[]')
    {
        return $this->parameter($name, $type)->optional();
    }

    /**
     * Add a parameter
     *
     * @param string $name
     * @param string $type
     * @param mixed  $default
     *
     * @return ParameterDefinitionBuilder
     */
    public function parameter($name, $type, $default = null)
    {
        $builder = new ParameterDefinitionBuilder();

        $builder->name($name);
        $builder->type($type);
        $builder->defaultValue($default);

        return $this->parameters[] = $builder;
    }

    /**
     * Set the return type
     *
     * @param string $type
     *
     * @return $this
     */
    public function returns($type)
    {
        $this->returnType = $type;

        return $this;
    }

    /**
     * Mark whether the method throws exception
     *
     * @param bool $throwingException
     *
     * @return $this
     */
    public function throwingException($throwingException = true)
    {
        $this->throwingException = (bool) $throwingException;

        return $this;
    }

    /**
     * Build the method definition
     *
     * @param ServiceDefinitionBuilder $serviceBuilder
     *
     * @return MethodDefinition
     */
    public function build(ServiceDefinitionBuilder $serviceBuilder)
    {
        $definition = new MethodDefinition($this->name);

        foreach ($this->parameters as $parameterBuilder) {
            $definition->addParameter($parameterBuilder->build($serviceBuilder));
        }

        if ($this->returnType) {
            $definition->setReturn($serviceBuilder->buildType($this->returnType));
        }

        $definition->setDescription($this->description);
        $definition->setThrowingException($this->throwingException);

        return $definition;
    }
}
