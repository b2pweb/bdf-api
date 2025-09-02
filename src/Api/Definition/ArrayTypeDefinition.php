<?php

namespace Bdf\Api\Definition;

/**
 * Class ArrayTypeDefinition
 *
 * @package Bdf\Api\Definition
 */
class ArrayTypeDefinition implements TypeDefinition
{
    /**
     * @var TypeDefinition
     */
    protected $internalType;


    /**
     * ArrayTypeDefinition constructor.
     *
     * @param TypeDefinition $type
     */
    public function __construct(TypeDefinition $type)
    {
        $this->internalType = $type;
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return $this->internalType->getName() . '[]';
    }

    /**
     * @return TypeDefinition
     */
    public function getInternalType()
    {
        return $this->internalType;
    }
}
