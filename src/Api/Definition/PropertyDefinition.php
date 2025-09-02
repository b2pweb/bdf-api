<?php

namespace Bdf\Api\Definition;

/**
 * Class PropertyDefinition
 *
 * @package Bdf\Api\Definition
 */
class PropertyDefinition
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var TypeDefinition
     */
    protected $type;

    /**
     * @var boolean
     */
    protected $nillable;

    /**
     * @var boolean
     */
    protected $optional;


    /**
     * PropertyDefinition constructor.
     *
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
     * @return PropertyDefinition
     */
    public function setName($name)
    {
        $this->name = $name;

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
     * @return PropertyDefinition
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @return boolean
     */
    public function isNillable()
    {
        return (boolean) $this->nillable;
    }

    /**
     * @param boolean $nillable
     *
     * @return PropertyDefinition
     */
    public function setNillable($nillable)
    {
        $this->nillable = $nillable;

        return $this;
    }

    /**
     * @return boolean
     */
    public function isOptional()
    {
        return (boolean) $this->optional;
    }

    /**
     * @param boolean $optional
     *
     * @return PropertyDefinition
     */
    public function setOptional($optional)
    {
        $this->optional = (bool) $optional;

        return $this;
    }
}
