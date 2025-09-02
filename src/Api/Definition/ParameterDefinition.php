<?php

namespace Bdf\Api\Definition;

/**
 * Class ParameterDefinition
 *
 * @package Bdf\Api\Definition
 */
class ParameterDefinition
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var int
     */
    protected $position;

    /**
     * @var TypeDefinition
     */
    protected $type;

    /**
     * @var mixed
     */
    protected $defaultValue;

    /**
     * @var boolean
     */
    protected $optional = false;

    /**
     * @var string
     */
    protected $description;


    /**
     * @param string $name
     * @param TypeDefinition $type
     */
    public function __construct($name, TypeDefinition $type)
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
     * @return int
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * @param int $position
     *
     * @return $this
     */
    public function setPosition($position)
    {
        $this->position = $position;

        return $this;
    }

    /**
     * @return TypeDefinition
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param TypeDefinition $type
     *
     * @return $this
     */
    public function setType(TypeDefinition $type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getDefaultValue()
    {
        return $this->defaultValue;
    }

    /**
     * @param mixed $defaultValue
     *
     * @return $this
     */
    public function setDefaultValue($defaultValue)
    {
        $this->defaultValue = $defaultValue;

        return $this;
    }

    /**
     * @return bool
     */
    public function isOptional()
    {
        return (bool) $this->optional;
    }

    /**
     * @param bool $optional
     *
     * @return $this
     */
    public function setOptional($optional)
    {
        $this->optional = (bool) $optional;

        return $this;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param string $description
     *
     * @return $this
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }
}
