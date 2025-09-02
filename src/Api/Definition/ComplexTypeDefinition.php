<?php

namespace Bdf\Api\Definition;

/**
 * Class ComplexTypeDefinition
 *
 * @package Bdf\Api\Definition
 */
class ComplexTypeDefinition implements TypeDefinition
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
     * @var PropertyDefinition[]
     */
    protected $properties = [];


    /**
     * ComplexTypeDefinition constructor.
     *
     * @param string $name
     * @param PropertyDefinition[] $properties
     */
    public function __construct($name, array $properties = [])
    {
        $this->name = $name;

        $this->setProperties($properties);
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return PropertyDefinition[]
     */
    public function getProperties()
    {
        return $this->properties;
    }

    /**
     * @param PropertyDefinition[] $properties
     *
     * @return ComplexTypeDefinition
     */
    public function setProperties(array $properties)
    {
        $this->properties = [];

        foreach ($properties as $property) {
            $this->addProperty($property);
        }

        return $this;
    }

    /**
     * @param PropertyDefinition $property
     *
     * @return ComplexTypeDefinition
     */
    public function addProperty(PropertyDefinition $property)
    {
        $this->properties[$property->getName()] = $property;

        return $this;
    }

    /**
     * @param string $name
     *
     * @return PropertyDefinition
     */
    public function getProperty($name)
    {
        return $this->properties[$name];
    }

    /**
     * @param string $name
     *
     * @return bool
     */
    public function hasProperty($name)
    {
        return isset($this->properties[$name]);
    }

    /**
     * @param string $realName
     *
     * @return ComplexTypeDefinition
     */
    public function setRealName($realName)
    {
        $this->realName = $realName;

        return $this;
    }

    /**
     * @return string
     */
    public function getRealName()
    {
        return $this->realName;
    }
}
