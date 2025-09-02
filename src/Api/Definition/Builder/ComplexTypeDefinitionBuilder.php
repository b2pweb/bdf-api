<?php

namespace Bdf\Api\Definition\Builder;

use Bdf\Api\Definition\ComplexTypeDefinition;

/**
 * Class ComplexTypeDefinitionBuilder
 *
 * @package Bdf\Api\Definition\Builder
 */
class ComplexTypeDefinitionBuilder
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $realName;

    /**
     * @var PropertyDefinitionBuilder[]
     */
    protected $properties = [];


    /**
     * Set the name of the complex type
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
     * Set the real name of the class
     *
     * @todo doesnt seem to be used
     *
     * @param string $realName
     *
     * @return $this
     */
    public function realName($realName)
    {
        $this->realName = $realName;

        return $this;
    }

    /**
     * Add an "object" property
     *
     * @param string $name
     *
     * @return PropertyDefinitionBuilder
     */
    public function object($name)
    {
        return $this->property($name, 'object');
    }

    /**
     * Add an "mixed" property
     *
     * @param string $name
     *
     * @return PropertyDefinitionBuilder
     */
    public function mixed($name)
    {
        return $this->property($name, 'mixed');
    }

    /**
     * Add an "string" property
     *
     * @param string $name
     *
     * @return PropertyDefinitionBuilder
     */
    public function string($name)
    {
        return $this->property($name, 'string');
    }

    /**
     * Add an "integer" property
     *
     * @param string $name
     *
     * @return PropertyDefinitionBuilder
     */
    public function integer($name)
    {
        return $this->property($name, 'integer');
    }

    /**
     * Add an "long" property
     *
     * @param string $name
     *
     * @return PropertyDefinitionBuilder
     */
    public function long($name)
    {
        return $this->property($name, 'long');
    }

    /**
     * Add an "float" property
     *
     * @param string $name
     *
     * @return PropertyDefinitionBuilder
     */
    public function float($name)
    {
        return $this->property($name, 'float');
    }

    /**
     * Add an "double" property
     *
     * @param string $name
     *
     * @return PropertyDefinitionBuilder
     */
    public function double($name)
    {
        return $this->property($name, 'double');
    }

    /**
     * Add an "boolean" property
     *
     * @param string $name
     *
     * @return PropertyDefinitionBuilder
     */
    public function boolean($name)
    {
        return $this->property($name, 'boolean');
    }

    /**
     * Add an "array" property supported only by rpc-encoded style
     *
     * @param string $name
     *
     * @return PropertyDefinitionBuilder
     */
    public function rpcArray($name)
    {
        return $this->property($name, 'array');
    }

    /**
     * Add a new property
     *
     * @param string $name
     * @param string $type
     *
     * @return PropertyDefinitionBuilder
     */
    public function property($name, $type)
    {
        $builder = new PropertyDefinitionBuilder();

        $builder->name($name);
        $builder->type($type);

        return $this->properties[$name] = $builder;
    }

    /**
     * Build the definition
     *
     * @param ServiceDefinitionBuilder $serviceBuilder
     *
     * @return ComplexTypeDefinition
     */
    public function build(ServiceDefinitionBuilder $serviceBuilder)
    {
        $definition = new ComplexTypeDefinition($this->name);

        foreach ($this->properties as $propertyBuilder) {
            $definition->addProperty($propertyBuilder->build($serviceBuilder));
        }

        $definition->setRealName($this->realName);

        return $definition;
    }
}
