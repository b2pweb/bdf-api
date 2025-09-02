<?php

namespace Bdf\Api\Mapping\Builder;

use Bdf\Api\Mapping\Registry;
use Bdf\Api\Mapping\Metadata\Types\ComplexTypeMetadata;

/**
 * TypeBuilder
 *
 * @package Bdf\Api\Mapping\Builder
 */
class TypeBuilder
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $class = 'stdClass';

    /**
     * @var TypePropertyBuilder[]
     */
    protected $properties = [];

    /**
     * @var Registry
     */
    protected $registry;


    /**
     * TypeBuilder constructor.
     *
     * @param Registry $registry
     */
    public function __construct(Registry $registry = null)
    {
        $this->registry = $registry ?: new Registry();
    }

    /**
     * Set the name of this type
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
     * Set the class name of this type
     *
     * @param string $class
     *
     * @return $this
     */
    public function className($class)
    {
        $this->class = $class;

        return $this;
    }

    /**
     * Add an "object" property
     *
     * @param string $name
     *
     * @return TypePropertyBuilder
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
     * @return TypePropertyBuilder
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
     * @return TypePropertyBuilder
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
     * @return TypePropertyBuilder
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
     * @return TypePropertyBuilder
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
     * @return TypePropertyBuilder
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
     * @return TypePropertyBuilder
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
     * @return TypePropertyBuilder
     */
    public function boolean($name)
    {
        return $this->property($name, 'boolean');
    }

    /**
     * Add an "array" property
     *
     * @param string $name
     *
     * @return TypePropertyBuilder
     */
    public function simpleArray($name)
    {
        return $this->property($name, 'array');
    }
    
    /**
     * Add a new property
     *
     * @param string $name
     * @param string $type
     *
     * @return TypePropertyBuilder
     */
    public function property($name, $type)
    {
        $builder = new TypePropertyBuilder($this->registry);

        $builder->name($name);
        $builder->type($type);

        return $this->properties[$name] = $builder;
    }

    /**
     * Add an embedded property
     *
     * @param string $name
     * @param string $complexType
     *
     * @return EmbeddedTypePropertyBuilder
     */
    public function embedded($name, $complexType)
    {
        $builder = new EmbeddedTypePropertyBuilder($this->registry);

        $builder->name($name);
        $builder->type($complexType);

        return $this->properties[$name] = $builder;
    }

    /**
     * Build the complex type metadata
     *
     * @param ServiceBuilder $serviceBuilder
     *
     * @return ComplexTypeMetadata
     */
    public function build(ServiceBuilder $serviceBuilder)
    {
        $metadata = new ComplexTypeMetadata($this->name, $this->class);

        foreach ($this->properties as $builder) {
            $metadata->addProperty($builder->build($serviceBuilder));
        }

        return $metadata;
    }
}
