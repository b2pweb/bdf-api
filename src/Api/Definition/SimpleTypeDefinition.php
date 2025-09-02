<?php

namespace Bdf\Api\Definition;

/**
 * @package Bdf\Api\Definition
 */
class SimpleTypeDefinition implements TypeDefinition
{
    /**
     * @var string
     */
    protected $name;


    /**
     * @param string $name
     */
    public function __construct($name)
    {
        $this->name = $name;
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return $this->name;
    }
}
